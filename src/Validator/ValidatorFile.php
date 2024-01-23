<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Validator;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidatorFile
{
    public function validateExcel(UploadedFile $file): bool
    {
        try {
            $allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            $allowedExtensions = ['xlsx', 'xls'];

            if (!in_array($file->getMimeType(), $allowedMimeTypes) || !in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
                throw new \InvalidArgumentException('Invalid file format. Only Excel files are allowed.');
            }

            return true;
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }
}
