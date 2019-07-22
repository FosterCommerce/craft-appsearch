<?php
namespace fostercommerce\appsearch\models;

use fostercommerce\appsearch\Plugin;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public $sync = false;
    public $apiEndpoint;
    public $publicSearchKey;
    public $privateApiKey;
    public $engines = [];
}
