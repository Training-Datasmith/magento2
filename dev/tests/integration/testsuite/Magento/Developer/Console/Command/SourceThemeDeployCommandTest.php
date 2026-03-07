<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Developer\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SourceThemeDeployCommandTest
 *
 * @see \Magento\Developer\Console\Command\SourceThemeDeployCommand
 */
class SourceThemeDeployCommandTest extends \PHPUnit\Framework\TestCase
{
    public const PUB_STATIC_DIRECTORY = 'pub/static';

    public const AREA_TEST_VALUE = 'frontend';

    public const LOCALE_TEST_VALUE = 'en_US';

    public const THEME_TEST_VALUE = 'Magento/luma';

    public const TYPE_TEST_VALUE = 'less';

    /**
     * @var SourceThemeDeployCommand
     */
    private $command;

    /**
     * @var string
     */
    private $pubStatic;

    /**
     * @var array
     */
    private $compiledFiles = ['css/styles-m', 'css/styles-l'];

    /**
     * Set up
     */
    protected function setUp(): void
    {
        global $installDir;

        $installDir = Bootstrap::getObjectManager()->create(
            Filesystem::class
        )->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();

        $this->pubStatic = $installDir . DIRECTORY_SEPARATOR . self::PUB_STATIC_DIRECTORY;
        $this->command = Bootstrap::getObjectManager()->get(SourceThemeDeployCommand::class);
    }

    /**
     * Run test for execute method
     */
    public function testExecute()
    {
        $error = [];

        /** @var OutputInterface|\PHPUnit\Framework\MockObject\MockObject $outputMock */
        $outputMock = $this->createMock(OutputInterface::class);

        $this->clearStaticDirectory();

        $this->command->run($this->getInputMock(), $outputMock);

        /** @var \SplFileInfo $file */
        foreach ($this->collectFiles($this->pubStatic) as $file) {
            $fileInfo = pathinfo($file->getFilename());
            if (!in_array('css/' . $fileInfo['filename'], $this->compiledFiles, true)
                && !$file->isLink()
            ) {
                $error[] = 'Bad file -> ' . $file->getFilename() . PHP_EOL;
            }
        }

        $this->clearStaticDirectory();

        self::assertEmpty($error, implode($error));
    }

    /**
     * @return void
     */
    private function clearStaticDirectory()
    {
        if (is_dir($this->pubStatic)) {
            /** @var \SplFileInfo $file */
            foreach ($this->collectFiles($this->pubStatic) as $file) {
                @unlink($file->getPathname());
            }
        }
    }

    /**
     * @param string $path
     * @return \RegexIterator|array
     */
    private function collectFiles($path)
    {
        $flags = \FilesystemIterator::CURRENT_AS_FILEINFO
            | \FilesystemIterator::SKIP_DOTS;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, $flags));

        return new \RegexIterator(
            $iterator,
            '#\.less$#',
            \RegexIterator::MATCH,
            \RegexIterator::USE_KEY
        );
    }

    /**
     * @return InputInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getInputMock()
    {
        $inputMock = $this->createMock(InputInterface::class);

        $inputMock->expects(self::exactly(4))
            ->method('getOption')
            ->willReturnMap(
                [
                    ['area', self::AREA_TEST_VALUE],
                    ['locale', self::LOCALE_TEST_VALUE],
                    ['theme', self::THEME_TEST_VALUE],
                    ['type', self::TYPE_TEST_VALUE],
                ]
            );
        $inputMock->expects(self::once())
            ->method('getArgument')
            ->with('file')
            ->willReturn($this->compiledFiles);

        return $inputMock;
    }
}
