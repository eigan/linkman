<?php

namespace Linkman;

use InvalidArgumentException;

use Linkman\Domain\File;

class Fileservice
{
    public function __construct(FilesystemResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function rename(File $file, $filename)
    {
        // validate prevent move file..
        $replaceName = $this->nameReplace($filename, $file);
        $originalName = $file->getPath();

        if ($replaceName == $originalName) {
            throw new InvalidArgumentException('Same filename');
        }

        // TODO: Authservice
        //$this->authService->ask(new RenameAction($originalName, $replaceName), function() {
            $this->filesystem($file)->rename($originalName, $replaceName);
        $file->setPath($replaceName);
        //});
    }

    public function realpath($file)
    {
        $filesystem = $this->filesystem($file);

        return $filesystem->realpath($file->getPath());
    }

    public function read($file)
    {
        return $this->filesystem($file)->read($file->getPath());
    }

    public function filesystem($file)
    {
        return $this->resolver->resolve($file);
    }

    private function nameReplace($string, $file)
    {
        $vars = [
            '{d}' => $file->getContent()->getCreatedAt()->format('d'),
            '{m}' => $file->getContent()->getCreatedAt()->format('m'),
            '{Y}' => $file->getContent()->getCreatedAt()->format('Y'),
            '{filename}' => $file->getFilename(),
            '{ext}' => $file->getExtension()
        ];

        return str_replace(array_keys($vars), array_values($vars), $string);
    }
}
