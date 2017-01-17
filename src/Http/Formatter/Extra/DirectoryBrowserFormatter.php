<?php

namespace Linkman\Http\Formatter\Extra;

use Linkman\Http\Formatter\FormatterInterface;

class DirectoryBrowserFormatter implements FormatterInterface
{
    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function format($file) : array
    {
        if ($file['type'] == 'dir') {
            $file['href'] = $this->apiUrl . '/browse?mount=' . $file['mount']['id'] . '&path=' . $file['path'];
        }

        if (isset($file['content'])) {
            $file['content']['href'] = $this->apiUrl . '/contents/' . $file['content']['id'];
        }

        return $file;
    }
}
