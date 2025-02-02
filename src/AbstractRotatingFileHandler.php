<?php declare(strict_types=1);
namespace Kingsoft\MonologHandler;

use Monolog\Level;
use Monolog\Utils;
use Cesargb\Log\Rotation;

// child must
abstract class AbstractRotatingFileHandler extends \Monolog\Handler\StreamHandler
{
  protected bool|null $mustRotate = null; // must rotate on next write
  private Rotation    $rotation;
  protected string    $filename; // base filename, rotation will add .1, .2, etc

  abstract protected function mustRotate(): bool;

  public function __construct(
    string $filename,
    int|string|Level $level = Level::Debug,
    null|array $rotateSettings = [],
    bool $bubble = false,
    ?int $filePermission = null,
    bool $useLocking = false
  ) {
    $this->setupRotator( $rotateSettings );

    $this->filename = Utils::canonicalizePath( $filename );
    if( !is_dir( \dirname( $this->filename ) ) ) {
      mkdir( \dirname( $this->filename ), 0777, true );
    }

    // Check if we must rotate on next write
    $this->mustRotate = $this->mustRotate();

    // Call parent constructor
    parent::__construct( $this->filename, $level, $bubble, $filePermission, $useLocking );
  }
  private function setupRotator( $rotateSettings ): void
  {
    if( empty( $rotateSettings ) ) {
      throw new \InvalidArgumentException( 'Missing rotateSettings' );
    }

    $maxFiles = (int) ( $rotateSettings['maxFiles'] ?? 10 );
    $minSize  = (int) ( $rotateSettings['minSize'] ?? 0 );
    $compress = (bool) ( $rotateSettings['compress'] ?? false );

    $this->rotation = ( new Rotation() )
      ->files( $maxFiles )
      ->minSize( $minSize );
    if( $compress ) {
      $this->rotation->compress();
    }
  }

  public function reset(): void
  {
    parent::reset();

    if( true === $this->mustRotate ) {
      $this->close();
      $this->rotate();
    }
  }
  /**
   * @inheritDoc
   */
  protected function write( \Monolog\LogRecord $record ): void
  {
    if( true === $this->mustRotate ) {
      $this->close();
      $this->rotate();
    }

    parent::write( $record );
  }

  protected function rotate(): void
  {
    $this->rotation->rotate( $this->filename );
  }

}
