<?php
namespace fostercommerce\appsearch\console\controllers;

use fostercommerce\appsearch\Plugin;

use Craft;
use yii\console\Controller;
use yii\helpers\Console;
use yii\console\Exception;
use yii\console\ExitCode;

class IndexController extends Controller
{
    public $defaultAction = 'refresh';

    public $force = false;

    public function options($actionID)
    {
        return ['force'];
    }

    public function actionFlush()
    {
        if ($this->force || $this->confirm(Craft::t('appsearch', 'Are you sure you want to flush AppSearch indices?'))) {
            foreach (Plugin::$instance->appsearchService->getMappings() as $mapping) {
                try {
                    // Try delete the engine. If it fails, the engine doesn't exist
                    Plugin::$instance->appsearchService->getClient()->deleteEngine($mapping->engine);
                } catch (\Exception $e) {
                    // Do nothing.
                }

                Plugin::$instance->appsearchService->getClient()->createEngine($mapping->engine);
            }

            return ExitCode::OK;
        }

        return ExitCode::OK;
    }

    public function actionImport()
    {
        foreach (Plugin::$instance->appsearchService->getMappings() as $mapping) {
            $elements = $mapping->getElementQuery()->all();

            $progress = 0;
            $total = count($elements);
            Console::startProgress(
                $progress,
                $total,
                Craft::t('appsearch', 'Creating index jobs for {engine}', ['engine' => $mapping->engine]),
                0.5
            );

            Plugin::$instance->appsearchService->indexElementsForMapping($mapping, $elements);

            Console::updateProgress($total, $total);
            Console::endProgress();
        }

        $this->stdout(Craft::t('appsearch', 'Running queue jobs...'.PHP_EOL), Console::FG_GREEN);
        Craft::$app->queue->run();

        return ExitCode::OK;
    }

    public function actionRefresh()
    {
        $this->actionFlush();
        $this->actionImport();

        return ExitCode::OK;
    }
}
