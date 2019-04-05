<?php

declare(strict_types=1);

namespace Bolt\Snippets;

class WeatherWidget extends BaseWidget
{
    protected $name = 'Weather Widget';
    protected $type = 'widget';
    protected $target = Target::WIDGET_BACK_DASHBOARD_ASIDE_TOP;
    protected $priority = 200;
    protected $template = '@bolt/widgets/weather.twig';
}
