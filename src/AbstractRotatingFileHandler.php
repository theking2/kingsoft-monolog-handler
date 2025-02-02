<?php declare(strict_types=1);
namespace Kingsoft\MonologHandler;

use Monolog\Level;
use Monolog\Utils;

abstract class AbstractRotatingFileHandler extends \Monolog\Handler\StreamHandler
{
  protected string             $filename;
  protected bool|null          $mustRotate     = null;
  protected \DateTimeImmutable $nextRotation;
  protected array              $rotateSettings;

  public function __construct(
    string $filename,
    int|string|Level $level = Level::Debug,
    null|array $rotateSettings = [],
    bool $bubble = false,
    ?int $filePermission = null,
    bool $useLocking = false
  ) {
    $this->filename       = Utils::canonicalizePath( $filename );
    $this->nextRotation   = $this->getNextRotation();
    $this->rotateSettings = $rotateSettings;

    parent::__construct( $this->filename, $level, $bubble, $filePermission, $useLocking );
  }
  /**
   * @inheritDoc
   */
  public function close(): void
  {
    parent::close();

    if( true === $this->mustRotate ) {
      $this->rotate();
    }
  }

  /**
   * @inheritDoc
   */
  public function reset(): void
  {
    parent::reset();

    if( true === $this->mustRotate ) {
      $this->rotate();
    }
  }
  /**
   * @inheritDoc
   */
  protected function write( \Monolog\LogRecord $record ): void
  {
    if( true === $this->mustRotate ) {
      $this->rotate();
    }
    if( $this->nextRotation <= $record->datetime ) {
      $this->mustRotate = true;
      $this->close();
    }
    parent::write( $record );
  }

  abstract protected function rotate(): void;

  abstract protected function getNextRotation(): \DateTimeImmutable;

}
