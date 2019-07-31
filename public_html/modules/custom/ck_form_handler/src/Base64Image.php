<?php

namespace Drupal\ck_form_handler;

use Drupal\image\Entity\ImageStyle;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Base64Image {

  protected $base64Image;

  protected $fileData;

  protected $fileName;

  protected $directory;

  public function decodeBase64Image() {
    $this->fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->base64Image));
    if ($this->fileData === FALSE) {
      throw new BadRequestHttpException('Image could not be processed.');
    }
    $f = finfo_open();
    $mimeType = finfo_buffer($f, $this->fileData, FILEINFO_MIME_TYPE);
    $ext = $this->getMimeTypeExtension($mimeType);
    $this->fileName = explode('.', $this->fileName)[0];
    $this->fileName .= uniqid(rand(), FALSE) . $ext;
  }

  /**
   * @return mixed
   */
  public function getFileData() {
    return $this->fileData;
  }

  /**
   * @return mixed
   */
  public function getFileName() {
    return $this->fileName;
  }

  /**
   * @param string $path
   */
  public function setFileDirectory($path) {
    $this->directory = \Drupal::service('file_system')
      ->realpath(file_default_scheme() . "://");
    $this->directory .= '/' . $path;
    file_prepare_directory($this->directory, FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY);
  }

  /**
   * @param mixed $base64Image
   */
  public function setBase64Image($base64Image) {
    $this->base64Image = $base64Image;
  }

  /**
   * @param mixed $fileName
   */
  public function setFileName($fileName) {
    $this->fileName = $fileName;
  }

  /**
   * @param string $mimeType
   *
   * @return string
   */
  protected function getMimeTypeExtension($mimeType) {
    $mimeTypes = [
      'image/png' => 'png',
      'image/jpeg' => 'jpg',
      'image/gif' => 'gif',
      'image/bmp' => 'bmp',
      'image/vnd.microsoft.icon' => 'ico',
      'image/tiff' => 'tiff',
      'image/svg+xml' => 'svg',
    ];
    if (isset($mimeTypes[$mimeType])) {
      return '.' . $mimeTypes[$mimeType];
    } else {
      $split = explode('/', $mimeType);
      return '.' . $split[1];
    }
  }
}
