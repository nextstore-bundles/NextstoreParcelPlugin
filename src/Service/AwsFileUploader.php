<?php

declare(strict_types=1);

namespace Nextstore\SyliusParcelPlugin\Service;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AwsFileUploader
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function upload(UploadedFile $file)
    {
        $dir = $this->parameterBag->get('nextstore.path.admin_excel');
        $filePathName = md5(uniqid()) . $file->getClientOriginalName();
        $fullPath = $dir . $filePathName;

        try {
            $file->move($dir, $filePathName);
        } catch (FileException $e) {
            throw new FileException($e->getMessage(), $e->getCode());
        }
        $s3Client = $this->getS3client();
        $bucket = $this->parameterBag->get('aws.s3.bucket');
        $s3Client->putObject([
            'Bucket' => $bucket,
            'Key' => $filePathName,
            'SourceFile' => $fullPath,
        ]);

        $s3Client->waitUntil('ObjectExists', [
            'Bucket' => $bucket,
            'Key' => $filePathName,
        ]);

        return $this->getFile($filePathName);
    }

    public function getFile($fileName)
    {
        $s3Client = $this->getS3client();
        $dir = $this->parameterBag->get('nextstore.path.admin_excel');
        $bucket = $this->parameterBag->get('aws.s3.bucket');

        try {
            $result = $s3Client->getObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
                'SaveAs' => $dir . $fileName,
            ]);

            return $dir . $fileName;
        } catch (S3Exception $e) {
            echo 'There was an error fetching data from S3: ' . $e->getMessage();
        } finally {
            $this->deleteObject($fileName);
        }
    }

    public function deleteObject($key): void
    {
        $bucket = $this->parameterBag->get('aws.s3.bucket');

        try {
            $s3Client = $this->getS3client();
            $s3Client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }
    }

    public function getS3client(): S3Client
    {
        $s3Client = new S3Client([
            'version' => $this->parameterBag->get('aws.s3.version'),
            'region' => $this->parameterBag->get('aws.s3.region'),
            'credentials' => [
                'key' => $this->parameterBag->get('aws.s3.key'),
                'secret' => $this->parameterBag->get('aws.s3.secret'),
            ],
        ]);

        return $s3Client;
    }
}
