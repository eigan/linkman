<?php

namespace Linkman\Http;

use Exception;
use Intervention\Image\ImageManager;
use Linkman\Http\Formatter\Domain\AlbumFormatter;
use Linkman\Http\Formatter\Domain\FileContentFormatter;
use Linkman\Http\Formatter\Domain\FileFormatter;
use Linkman\Http\Formatter\Domain\MountFormatter;
use Linkman\Http\Formatter\Domain\TagFormatter;
use Linkman\Http\Formatter\Extra\DirectoryBrowserFormatter;
use Linkman\Http\Response\CollectionResponse;
use Linkman\Http\Response\EntityResponse;
use Linkman\Http\Response\PaginatedResponse;
use Linkman\Http\Response\ResourceResponse;
use Linkman\Linkman;
use Opulence\Http\Requests\Request;
use Opulence\Http\Responses\JsonResponse;
use Opulence\Http\Responses\Response;
use Opulence\Http\Responses\ResponseHeaders;
use Opulence\Ioc\Container;
use Opulence\Routing\Dispatchers\ContainerDependencyResolver;
use Opulence\Routing\Dispatchers\MiddlewarePipeline;
use Opulence\Routing\Dispatchers\RouteDispatcher;
use Opulence\Routing\Router;
use Opulence\Routing\Routes\Compilers\Compiler;
use Opulence\Routing\Routes\Compilers\Matchers\HostMatcher;
use Opulence\Routing\Routes\Compilers\Matchers\PathMatcher;
use Opulence\Routing\Routes\Compilers\Matchers\SchemeMatcher;
use Opulence\Routing\Routes\Compilers\Parsers\Parser;

/**
 * God class of the Api
 *
 * Will fix it up and split when the times comes
 */
class Kernel
{
    const VERSION = 1;

    /**
     * @var Linkman
     */
    private $linkman;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Container
     */
    private $container;

    public function __construct(Linkman $linkman)
    {
        $this->linkman = $linkman;
    }

    public function start()
    {
        $this->container = new Container();

        $dispatcher = new RouteDispatcher(
            new ContainerDependencyResolver($this->container),
            new MiddlewarePipeline()
        );
        $compiler = new Compiler([new PathMatcher(), new HostMatcher(), new SchemeMatcher()]);
        $parser = new Parser();
        $this->router = new Router($dispatcher, $compiler, $parser);

        // TODO: Prefix API with v1
        // TODO: Skip api.php
        $this->router->group(['path' => '/api/v1'], function (Router $router) {
            $this->register($router);
        });

        $this->router->get('/favicon.ico', function() {
            return new Response('', ResponseHeaders::HTTP_NOT_FOUND);
        });
    }

    /**
     * Adds all the standard api stuff for v1
     */
    private function register(Router $router)
    {
        $router->get('', function () {
            return new JsonResponse([
                'linkman' => Linkman::VERSION
            ]);
        });

        $router->get('/albums', function () {
            return new CollectionResponse($this->linkman->api()->albums(), new AlbumFormatter($this->getBaseUrl()));
        });

        $router->post('/albums', function(Request $request) {
            $title = $request->getInput('title');

            if(empty($title)) {
                return new JsonResponse(['message' => "Missing 'title' value"], ResponseHeaders::HTTP_BAD_REQUEST);
            }

            $album = $this->linkman->api()->albums->create($title);

            return new EntityResponse($album, new AlbumFormatter($this->getBaseUrl()));
        });

        $router->get('/albums/:albumId', function (int $albumId) {
            return new EntityResponse($this->linkman->api()->album($albumId), new AlbumFormatter($this->getBaseUrl()));
        });

        $router->get('/albums/:albumId/contents', function (int $albumId) {
            $contents = $this->linkman->api()->albums->contents($albumId);

            return new PaginatedResponse($contents, new FileContentFormatter($this->getBaseUrl()));
        });

        $router->post('/albums/:albumId/contents', function (Request $request, int $albumId) {
            $album = $this->linkman->api()->album($albumId);
            $contentId = $request->getInput('contentId');

            if ($contentId == null) {
                new Response('', ResponseHeaders::HTTP_BAD_REQUEST);
            }

            $content = $this->linkman->api()->content($contentId);

            if ($content == null) {
                new Response('', ResponseHeaders::HTTP_BAD_REQUEST);
            }

            $album->addContent($content);
            $this->linkman->api()->flush();

            return new Response();
        });

        $router->get('/browse', function (Request $request) {
            $options = [
                'mountId' => $request->getInput('mount'),
                'path' => $request->getInput('path', '/')
            ];
            $files = $this->linkman->api()->files($options['mountId'], $options['path']);

            return new CollectionResponse($files, new DirectoryBrowserFormatter($this->getBaseUrl()));
        });

        $router->get('/calendar', function() {
            $years = $this->linkman->api()->contents->yearsCount();

            $years = array_map(function($year) {
                return [
                    'year' => $year['year'],
                    'href' => $this->getBaseUrl() . '/calendar/' . $year['year'],
                    'months' => [
                        'href' => $this->getBaseUrl() . '/calendar/' . $year['year'] . '/months',
                    ],
                    'contents' => [
                        'href' => $this->getBaseUrl() . '/calendar/' . $year['year'] . '/contents',
                        'count' => $year['count']
                    ]
                ];
            }, $years);

            return new JsonResponse($years);
        });

        // TODO: ?embed=days
        $router->get('/calendar/:year/months', function($year) {
            $months = $this->linkman->api()->contents->monthsCount($year);

            $months = array_map(function($month) {
                return [
                    'month' => $month['month'],
                    'href' => $this->getBaseUrl() . '/calendar/' . $month['year'] . '/months/' . $month['month'],
                    'days' => [
                        'href' => $this->getBaseUrl().'/calendar/'.$month['year'].'/months/'.$month['month'].'/days',
                    ],
                    'contents' => [
                        'count' => $month['count'],
                        'href' => $this->getBaseUrl().'/calendar/'.$month['year'].'/months/'.$month['month'].'/contents',
                    ],
                ];
            }, $months);

            return new JsonResponse($months);
        });

        $router->get('/calendar/:year/contents', function(Request $request, $year) {
            $request->getQuery()->add('filter-year', $year);
            $request->setPath('/api/v1/contents');

            return $this->router->route($request);
        });

        $router->get('/calendar/:year/months/:month/days', function($year, $month) {
            // List all days in month
            $days = $this->linkman->api()->contents->daysCount($year, $month);

            $days = array_map(function($day) {
                return [
                    'day' => $day['day'],
                    'contents' => [
                        'count' => $day['count'],
                        'href' => $this->getBaseUrl().'/calendar/'.$day['year'].'/months/'.$day['month'].'/days/'.$day['day'].'/contents',
                    ]
                ];
            }, $days);

            return new JsonResponse($days);
        });

        $router->get('/calendar/:year/months/:month/contents', function(Request $request, $year, $month) {
            // List all contents in month

            $request->getQuery()->add('filter-year', $year);
            $request->getQuery()->add('filter-month', $month);
            $request->setPath('/api/v1/contents');

            return $this->router->route($request);
        });

        $router->get('/calendar/:year/months/:month/days/:day/contents', function(Request $request, $year, $month, $day) {
            // List all contents in month

            // TODO: Add these filter-year, filter-month, filter-day
            $request->getQuery()->add('filter-year', $year);
            $request->getQuery()->add('filter-month', $month);
            $request->getQuery()->add('filter-day', $day);

            $request->setPath('/api/v1/contents');

            return $this->router->route($request);
        });

        $router->get('/contents', function (Request $request) {
            $paginator = $this->linkman->api()->contents->all($request->getQuery()->getAll());
            $pageCount = $request->getInput('pageCount', 10);
            $currentPage = $request->getInput('page', 1);

            $paginator->getQuery()->setMaxResults($pageCount);
            $paginator->getQuery()->setFirstResult(($currentPage - 1) * $pageCount);

            $response = new PaginatedResponse($paginator, new FileContentFormatter($this->getBaseUrl()));

            $response->setNextLink($this->getBaseUrl() . '/contents?page='.($currentPage + 1));

            if ($currentPage > 1) {
                $response->setPreviousLink($this->getBaseUrl() . '/contents?page='.($currentPage - 1));
            }

            return $response;
        });

        $router->get('/contents/:contentId', function ($contentId) {
            return new EntityResponse($this->linkman->api()->content($contentId), new FileContentFormatter($this->getBaseUrl()));
        });

        $router->post('/contents/:contentId', function ($contentId, Request $request) {
            $content = $this->linkman->api()->content($contentId);

            $visibility = $request->getInput('hidden');

            if ($visibility) {
                if ($visibility === 'true') {
                    $visibility = true;
                }
                if ($visibility === 'false') {
                    $visibility = false;
                }

                $content->setHidden($visibility);
            }

            // Visibility
            // location
            // license

            $this->linkman->api()->flush();

            return new EntityResponse($content, new FileContentFormatter($this->getBaseUrl()));
        });

        $router->get('/contents/:contentId/albums', function ($contentId) {
            $content = $this->linkman->api()->content($contentId);

            return new CollectionResponse($content->getAlbums(), new AlbumFormatter($this->getBaseUrl()));
        });

        $router->post('/contents/:contentId/favorite', function ($contentId) {
            return new EntityResponse($this->linkman->api()->content($contentId), new FileContentFormatter($this->getBaseUrl()));
        });

        $router->get('/contents/:contentId/files', function ($contentId, Request $request) {
            $content = $this->linkman->api()->content($contentId);

            $embeds = explode(',', $request->getInput('embed', ''));

            return new CollectionResponse($content->getFiles(), new FileFormatter($this->getBaseUrl(), $embeds));
        });

        $router->get('/contents/:contentId/tags', function ($contentId) {
            $content = $this->linkman->api()->content($contentId);

            return new CollectionResponse($content->getTags(), new TagFormatter($this->getBaseUrl()));
        });

        $router->delete('/contents/:contentId/tags', function(Request $request, $contentId) {
            $content = $this->linkman->api()->content($contentId);

            $tags = $request->getInput('tags');

            if(!is_array($tags) || empty($tags)) {
                return new JsonResponse(['message' => "Need 'tags' as array."], ResponseHeaders::HTTP_BAD_REQUEST);
            }

            foreach($tags as $tagName) {
                $content->getTags()->remove($tagName);
            }

            $this->linkman->api()->flush();

            return new CollectionResponse($content->getTags(), new TagFormatter($this->getBaseUrl()));
        });

        $router->get('/contents/:contentId/raw', function (Request $request, int $contentId) {
            $format = $request->getInput('format', 'original');

            // TODO: Make resource and info

            $content = $this->linkman->api()->content($contentId);

            if ($content === null) {
                return new Response('', ResponseHeaders::HTTP_NOT_FOUND);
            }

            $content = $this->linkman->api()->content($contentId);

            if ($format == 'thumb') {
                $cachePath = $this->getCachePath() . '/'.$content->getHash(). '.thumb';
                if (file_exists($cachePath)) {
                    $resource = fopen($cachePath, 'r');
                    return new ResourceResponse($resource, fstat($resource)['size'], $content->getFiletype());
                }

                $manager = new ImageManager(['driver' => 'imagick']);

                $resource = $this->linkman->api()->contentRaw($contentId);
                $image = $manager->make($resource);

                $image->resize(null, 500, function ($constraint) {
                    $constraint->aspectRatio();
                });

                if ($content->getOrientationAdjust()) {
                    $image->rotate($content->getOrientationAdjust());
                }

                $resource = $image->stream(null, 40)->detach();
                file_put_contents($cachePath, $resource);
                return new ResourceResponse($image->stream(null, 40)->detach(), fstat($resource)['size'], $content->getFiletype());
            }

            if ($resource && $content) {
                return new ResourceResponse($resource, $content->getSize(), $content->getFiletype());
            }

            return new Response('', ResponseHeaders::HTTP_NOT_FOUND);
        });

        $router->get('/files', function (Request $request) {
            $options = [
                'mountId' => $request->getInput('mount'),
                'path' => $request->getInput('path', '/')
            ];

            return new CollectionResponse($this->linkman->api()->files($options['mountId'], $options['path']), new FileFormatter($this->getBaseUrl()));
        });

        $router->get('/files/:fileId', function ($fileId) {
            return new EntityResponse($this->linkman->api()->file($fileId), new FileFormatter($this->getBaseUrl()));
        });

        $router->get('/mounts', function () {
            return new CollectionResponse($this->linkman->api()->mounts(), new MountFormatter($this->getBaseUrl()));
        });

        $router->get('/mounts/:mountId', function ($mountId) {
            $mount = $this->linkman->api()->mount($mountId);

            return new EntityResponse($mount, new MountFormatter($this->getBaseUrl()));
        });

        $router->get('/search/contents', function (Request $request) {

            // Get all $request, and send it?
            $query = $request->getInput('q', '');

            $paginator = $this->linkman->api()->contents->search($query);

            $pageCount = $request->getInput('pageLength', 10);
            $currentPage = $request->getInput('page', 1);

            $paginator->getQuery()->setMaxResults($pageCount);
            $paginator->getQuery()->setFirstResult(($currentPage - 1) * $pageCount);

            $response = new PaginatedResponse($paginator, new FileContentFormatter($this->getBaseUrl()));

            $response->setNextLink($this->getBaseUrl() . '/contents?page='.($currentPage + 1));

            if ($currentPage > 1) {
                $response->setPreviousLink($this->getBaseUrl() . '/contents?page='.($currentPage - 1));
            }

            return $response;
        });

        $router->get('/tags', function (Request $request) {
            $also = $request->getInput('with', '');
            $also = explode(',', $also);
            return new CollectionResponse($this->linkman->api()->tags->also($also), new TagFormatter());
        });
    }

    public function handle(Request $request)
    {
        if ($this->router === null) {
            throw new Exception('Run start() first');
        }

        $this->request = $request;

        $this->container->bindInstance(Request::class, $request);

        return $this->router->route($request);
    }

    /**
     * Takes the Request url (from handle()..) and replaces everything after /api/vN)
     */
    private function getBaseUrl() : string
    {
        return preg_replace("/(?<=\/api\/v\d)(.*)/", '', $this->request->getFullUrl());
    }

    private function getCachePath() : string
    {
        $path = sys_get_temp_dir() . '/linkman';

        if (file_exists($path) == false) {
            mkdir($path);
        }

        return $path;
    }
}

/*
TODO: List of endpoints I need

/timeline?start=20-01-2017
// Groups by day
// embed: items
[
    {
        day: ...
        contents: {
            href: ...
            count: ..
        },

        main: {
            id: most fav/likes/pop id
        },

        albums: [
            href:
            count: ..
        ]
    }
]

/timeline/20-01-2017/albums
/timeline/20-01-2017/contents
[
    ...
]

*/
