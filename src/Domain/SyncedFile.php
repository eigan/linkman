<?php

namespace Linkman\Domain;

class SyncedFile
{
    /**
     * one sync
     */
    protected $sync;

    /**
     * File
     */
    protected $file;

    /**
     * What did happen with the file..
     */
    protected $action;
}
