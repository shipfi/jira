<?php

namespace tests\codeception\frontend\_pages\demo;

use yii\codeception\BasePage;

/**
 * Represents about page
 * @property \codeception_frontend\AcceptanceTester|\codeception_frontend\FunctionalTester $actor
 */
class AboutPage extends BasePage
{
    public $route = 'demo/site/about';
}
