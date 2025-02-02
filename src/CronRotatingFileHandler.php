<?php declare(strict_types=1);

namespace Kingsoft\MonologHandler;

/*
 * (c) TheKing2 <theking2@king.ma>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \DateTimeImmutable as DTi;
use Monolog\Level;
use Monolog\Utils;
use Monolog\Handler;
use \Cron\CronExpression;

/**
 * Stores logs rotated by cron expression
 *
 * This rotation is only intended to be used as a workaround. Using logrotate to
 * handle the rotation is strongly encouraged when you can use it.
 *
 */
class CronRotatingFileHandler extends AbstractRotatingFileHandler implements Handler\HandlerInterface
{
  private                $stateFilename;
  private CronExpression $cron;

  /**
   * @param $filename The log file path
   * @param $level The minimum logging level at which this handler will be triggered
   * @param $rotateSettings The settings for the log rotation
   * @param $bubble Whether the messages that are handled can bubble up the stack or not
   * @param $filePermission Optional file permissions (default (0644) are only for owner read/write)
   * @param $useLocking Try to lock log file before doing any writes
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   * rotateSettings include:
   * - cronExpression: The cron expression to use for rotation
   * - maxFiles: The maximum number of files to keep (0 means unlimited)
   * - minSize: The minimum size of the file to rotate
   * - compress: Whether to compress the files or not
   */
  public function __construct(
    string $filename,
    int|string|Level $level,
    array $rotateSettings,
    bool $bubble = false,
    ?int $filePermission = null,
    bool $useLocking = false
  ) {
    $this->stateFilename = Utils::canonicalizePath( $filename . '.state' );
    $this->cron          = new CronExpression( $rotateSettings['cronExpression'] ?? '* * */1 * *' );

    parent::__construct( $filename, $level, $rotateSettings, $bubble, $filePermission, $useLocking );
  }

  protected function mustRotate(): bool
  {
    if( !file_exists( $this->stateFilename ) ) {
      touch( $this->stateFilename );
    }
    $dateTime     = new DTI();                                        // current datetime
    $fileInfo     = new \SplFileInfo( $this->stateFilename );         // state-file info
    $filedateTime = $dateTime->setTimeStamp( $fileInfo->getMTime() ); // state-file datetime
    $nextRun      = $this->cron->getNextRunDate( $filedateTime );     // next-run datetime

    // true if current datetime is greater than or equal to next-run datetime
    return $dateTime >= $nextRun;
  }
}
