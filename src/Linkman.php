<?php

namespace Linkman;

use DateTime;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Tools\SchemaTool;

use Doctrine\ORM\Tools\Setup;

use Doctrine\ORM\Tools\ToolsException;
use Exception;

use InvalidArgumentException;

use League\Container\Container;

use League\Container\ReflectionContainer;
use Linkman\Api\Api;

use Linkman\Domain\File;

use Linkman\Domain\FileContent;

use Linkman\Domain\Mount;
use Linkman\Domain\PluginDefinition;

use Linkman\Domain\Tag;
use Linkman\Domain\UnknownContentType;
use Linkman\Exception\AlreadyInitializedException;
use Linkman\Exception\NotInitializedException;

use Linkman\Plugin\ContentActionInterface;

use Linkman\Plugin\ContentOutputInterface;
use Linkman\Plugin\ContentQueryModifierInterface;
use Linkman\Plugin\CorePlugin;
use Linkman\Repositories\EntityRepository;

use Monolog\Handler\StreamHandler;

use Monolog\Logger;
use RuntimeException;

class Linkman
{
    const VERSION = '0.1-DEV';

    const SYNC_FORCE = 2;

    private $path;

    private $hooks;

    public function __construct($path)
    {
        $this->path = $path;
        $this->modifiers = new ContentQueryModifierCollection();
        $this->callables = new ContentQueryModifierCollection();
        $this->hooks = [];

        $this->filesystemResolver = new FilesystemResolver();
        $this->fileservice = new Fileservice($this->filesystemResolver);
        $this->log = new Logger('linkman');
        $this->log->pushHandler(new StreamHandler('linkman.log', Logger::DEBUG));

        $this->connect($path);
    }

    /**
     *
     */
    private function connect($path)
    {
        $dbPath = $path . 'linkman.db';
        $isDevMode = true;
        $doctrineConfig = Setup::createYAMLMetadataConfiguration([__DIR__.'/mappings'], $isDevMode);

        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => $dbPath,
        ];

        $this->container = new Container();
        $this->container->delegate(
            new ReflectionContainer()
        );

        $doctrineConfig->setDefaultRepositoryClassName(EntityRepository::class);

        $doctrineConfig->addCustomDatetimeFunction('year', \DoctrineExtensions\Query\Sqlite\Year::class);
        $doctrineConfig->addCustomDatetimeFunction('month', \DoctrineExtensions\Query\Sqlite\Month::class);
        $doctrineConfig->addCustomDatetimeFunction('day', \DoctrineExtensions\Query\Sqlite\Day::class);

        $this->entityManager = EntityManager::create($conn, $doctrineConfig);

        $this->pluginDefinitions = $this->entityManager->getRepository(PluginDefinition::class);
        $this->mounts = $this->entityManager->getRepository(Mount::class);
        $this->contents = $this->entityManager->getRepository(FileContent::class);
        $this->files = $this->entityManager->getRepository(File::class);
        $this->tags = $this->entityManager->getRepository(Tag::class);

        $this->tagservice = new Tagservice($this->tags);

        $this->container->add(get_class($this->contents), $this->contents);
        $this->container->add(get_class($this->filesystemResolver), $this->filesystemResolver);
        $this->container->add(get_class($this->files), $this->files);
        $this->container->add(get_class($this->tags), $this->tags);
        $this->container->add(EntityManagerInterface::class, $this->entityManager);
        $this->container->add(ContentQueryModifierCollection::class, $this->modifiers);
        $this->container->add(get_class($this->tagservice), $this->tagservice);

        if (file_exists($path . 'linkman.php')) {
            $userDefined = require $path . 'linkman.php';

            if (is_array($userDefined) == false) {
                throw new RuntimeException('The linkman.php file needs to return an array');
            }

            if (isset($userDefined['hooks'])) {
                foreach ($userDefined['hooks'] as $hook => $callback) {
                    $this->hook($hook, $callback);
                }
            }

            if (isset($userDefined['register']) && is_callable($userDefined['register'])) {
                $userDefined['register']($this);
            }
        }

        try {
            foreach ($this->pluginDefinitions->findAll() as $pluginDefinition) {
                $className = $pluginDefinition->getClassName();
                $plugin = $this->make($className, [
                    'options' => $pluginDefinition->getOptions(),
                    $this->fileservice,
                    $this->tagservice
                ]);

                foreach ($plugin->hooks() as $event => $eventCallbacks) {
                    if (!is_array($eventCallbacks)) {
                        continue;
                    }

                    foreach ($eventCallbacks as $eventCallback) {
                        $this->hook($event, $eventCallback);
                    }
                }

                $plugin->register($this);
            }
        } catch (TableNotFoundException $e) {
            throw new NotInitializedException($this);
        }
    }

    /**
     * Setup the database schema
     */
    public function initialize()
    {
        try {
            $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $schemaTool = new SchemaTool($this->entityManager);
            $schemaTool->createSchema($metadatas);
        } catch (ToolsException $e) {
            throw new AlreadyInitializedException();
        }

        // Add default plugins
        $this->install(CorePlugin::class);
    }

    /**
     * Destroy the database schema
     */
    public function destroy()
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
    }

    /**
     * Get the filesystem of a Mount
     */
    public function filesystem($mount) : Filesystem
    {
        return $this->filesystemResolver->resolve($mount);
    }

    /**
     * Store all modifications
     */
    public function flush()
    {
        $this->entityManager->flush();
    }

    /**
     * @return Api
     */
    public function api()
    {
        return new Api($this->container);
    }

    /**
     *
     */
    public function hook(string $hook, callable $callback)
    {
        if (isset($this->hooks[$hook]) == false) {
            $this->hooks[$hook] = [];
        }

        if (strpos($hook, 'action') === 0 && $callback instanceof ContentActionInterface == false) {
            throw new Exception('All action hooks should implement ContentActionInterface');
        }

        $this->hooks[$hook][] = $callback;
    }

    /**
     * Extends Linkman
     */
    public function use($object)
    {
        if ($object instanceof ContentQueryModifierInterface) {
            $this->modifiers[$object->getName()] = $object;
        }

        if ($object instanceof ContentActionInterface ||
           $object instanceof ContentOutputInterface
        ) {
            $this->callables[] = $object;
        }
    }

    public function callables($type)
    {
        return $this->callables->getByType($type);
    }

    public function modifiers()
    {
        return $this->modifiers;
    }

    public function hooks($grep)
    {
        $hooks = [];

        foreach ($this->hooks as $hookName => $hookCallbacks) {
            if (empty($grep) || strpos($hookName, $grep) === 0) {
                $hooks = array_merge($hooks, $hookCallbacks);
            }
        }

        return $hooks;
    }

    public function getHooks()
    {
        return $this->hooks;
    }

    public function install($className, $options = [])
    {
        if (class_exists($className) == false) {
            throw new InvalidArgumentException("Plugin [$className] does not exist.");
        }

        $pluginDefinition = new PluginDefinition($className, $options);
        $this->entityManager->persist($pluginDefinition);
        $this->entityManager->flush();
    }

    public function sync(string $path = null)
    {
        // Get mount from $path
        foreach ($this->mounts->match($path) as $mount) {
            $this->syncMount($mount, $path);
        }
    }

    public function tags()
    {
        return $this->tags->findAll();
    }

    public function mount($target, $name, $config = [])
    {
        return $this->api()->mounts->create($target, $name, $config);
    }

    public function syncMount($mount, $path = null, $mode = 0)
    {
        $filesystem = $this->filesystem($mount);
        $force = $mode == self::SYNC_FORCE;

        try {
            $contents = $filesystem->listContents($path, true);
        } catch (\Exception $e) {
            return; // Possible no permission to read..
        }

        $this->log->debug("Start sync: Mount({$mount->getName()}), Path == '$path'");

        $countSinceFlush = 0;
        foreach ($contents as $localFile) {
            $countSinceFlush++;
            $relativePath = $localFile['path'];

            if ($localFile['type'] == 'dir') {
                continue;
            }

            $this->callHook('sync.file.start', ['path' => $relativePath]);

            if ($filesystem->isReadable($relativePath) == false) {
                $this->log->debug("Skip unreadable file: $relativePath");
                continue;
            }

            $this->log->debug("Start sync file: $relativePath " . ($force ? 'force' : 'no-force'));

            $modifiedAt = $filesystem->modified($relativePath);

            $file = $this->files->byPath($relativePath, $mount);

            $isNewFile = ($file instanceof File == null);

            if ($isNewFile) {
                $this->log->debug('File is new');

                // File might be moved, so we try to see if we have stored a similiar
                // photo before
                $this->log->debug('Check hash');
                $hash = $filesystem->hashOf($relativePath);
                $this->log->debug('Hash:' . $hash);

                $fileContent = $this->contents->byHash($hash);

                if ($fileContent instanceof FileContent == false) {
                    try {
                        $fileContent = $filesystem->resolveContent($relativePath);
                        $this->log->debug('File is: ' . get_class($fileContent));
                    } catch (InvalidArgumentException $e) {
                        $this->log->debug("Skip file {$e->getMessage()}");
                        continue;
                    } catch (RuntimeException $e) {
                        $this->log->error('Wrong on sync for file..');
                        $this->log->error('Exception: ' . $e->getMessage());
                        continue;
                    }
                }

                $this->entityManager->persist($fileContent);

                $lastSynced = new DateTime('1900-01-01'); // Never synced..
                $file = new File($mount, $relativePath, $fileContent, $modifiedAt, $lastSynced);
            }

            if ($isNewFile == false && $file->getContent() instanceof UnknownContentType) {
                // Resolve again
                $this->log->debug('Resolve again');
            }

            $lastSync = $file->getLastSynced();
            if ($force || $isNewFile || $lastSync < $modifiedAt) {
                $content = $file->getContent();

                $content->setSize($localFile['size']);

                $content->setModifiedAt(new DateTime(date('c', $localFile['timestamp'])));

                $this->callHook('sync', [$content, $file]);

                $file->setLastSynced(new DateTime());

                $this->entityManager->persist($content);
                $this->entityManager->persist($file);
            }

            if ($countSinceFlush > 100) {
                $countSinceFlush = 0;
                $this->entityManager->flush();
            }
        }

        $this->log->debug('Start flush');
        $this->entityManager->flush();
        $this->log->debug('End flush');
    }

    /**
     * Delete files no longer exists
     */
    public function cleanup()
    {
        foreach ($this->files->findAll() as $file) {
            $filesystem = $this->filesystem($file->getMount());

            if ($filesystem->has($file->getPath()) == false) {
                $this->entityManager->remove($file);
            }
        }
    }

    private function make($className, $contained = [])
    {
        $container = new Container();

        $container->delegate(
            new ReflectionContainer()
        );

        $contained[] = $this->log;
        $contained[] = $this->api();

        foreach ($contained as $name => $object) {
            if (is_object($object)) {
                $container->add(get_class($object), $object);
            } else {
                $container->add($name, $object);
            }
        }

        return $container->get($className);
    }

    private function callHook($hook, $contained = [])
    {
        if (isset($this->hooks[$hook]) == false) {
            return;
        }

        $contained[] = $this->log;
        $contained[] = $this->api();

        $container = new Container();

        foreach ($contained as $objectKey => $object) {
            if (is_string($object)) {
                $container->add($objectKey, $object);
                continue;
            }

            if ($object instanceof FileContent) {
                $container->add(FileContent::class, $object);
            }

            $container->add(get_class($object), $object);
        }

        $this->log->debug("Calling hook $hook on " . count($this->hooks[$hook]) . ' callbacks');

        foreach ($this->hooks[$hook] as $callable) {
            $container->call($callable);
        }
    }
}
