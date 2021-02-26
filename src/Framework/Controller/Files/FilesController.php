<?php

namespace Survey54\Reap\Framework\Controller\Files;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\FileService;

abstract class FilesController extends Controller
{
    protected FileService $fileService;

    /**
     * FilesController constructor.
     * @param FileService $fileService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(FileService $fileService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->fileService = $fileService;
    }
}
