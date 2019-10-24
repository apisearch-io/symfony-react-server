<?php declare(strict_types=1);

namespace Apisearch\SymfonyReactServer;

use React\EventLoop\LoopInterface;
use seregazhuk\PhpWatcher\Config\WatchList;
use seregazhuk\PhpWatcher\Filesystem\ChangesListener;
use seregazhuk\PhpWatcher\Screen\Screen;
use seregazhuk\PhpWatcher\Screen\SpinnerFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ChangesWatcher
{
    private const DIRECTORIES = ['config', 'src'];
    private const EXTENSIONS = ['php', 'yml', 'yaml', 'xml'];

    private $loop;
    private $watchList;
    private $screen;

    public static function isAvailable(): bool
    {
        return class_exists('\seregazhuk\PhpWatcher\Config\WatchList');
    }

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->watchList = new WatchList(self::DIRECTORIES, self::EXTENSIONS);
        $output = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        $this->screen = new Screen($output, SpinnerFactory::create($output, false));
    }

    public function watch(Application $application): void
    {
        $this->screen->showSpinner($this->loop);
        $filesystemListener = new ChangesListener($this->loop, $this->watchList);

        $filesystemListener->on('change', function () use ($application) {
            $this->restartApplication($application);
        });

        $filesystemListener->start();
    }

    private function restartApplication(Application $application): void
    {
        $this->screen->restarting();
        $application->stop();
        $application->run();
    }
}
