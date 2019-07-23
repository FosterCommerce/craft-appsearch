<?php
namespace fostercommerce\appsearch;

use fostercommerce\appsearch\services\AppsearchService;
use fostercommerce\appsearch\variables\AppsearchVariable;
use fostercommerce\appsearch\models\Settings;

use Craft;
use craft\base\Element;
use craft\base\Plugin as BasePlugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\events\ModelEvent;
use craft\console\Application as ConsoleApplication;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

class Plugin extends BasePlugin
{
    public static $instance;

    public function init()
    {
        parent::init();

        $this->setComponents([
            'appsearchService' => AppsearchService::class,
        ]);

        self::$instance = $this;

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'fostercommerce\appsearch\console\controllers';
        }

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('appsearch', AppsearchVariable::class);
            }
        );

        Event::on(
            Element::class,
            Element::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                if ($this->getSettings()->sync) {
                    $this->indexElements($event->sender);
                }
            }
        );

        Event::on(
            Element::class,
            Element::EVENT_AFTER_MOVE_IN_STRUCTURE,
            function (ModelEvent $event) {
                if ($this->getSettings()->sync) {
                    $this->indexElements($event->sender);
                }
            }
        );

        Event::on(
            Element::class,
            Element::EVENT_BEFORE_DELETE,
            function (ModelEvent $event) {
                if ($this->getSettings()->sync) {
                    $this->deleteElements($event->sender);
                }
            }
        );
    }

    protected function indexElements($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        self::$instance->appsearchService->indexElements($elements);
    }

    protected function deleteElements($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        self::$instance->appsearchService->deleteElements($elements);
    }


    protected function createSettingsModel()
    {
        return new Settings();
    }
}
