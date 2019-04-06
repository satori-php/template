# Template engine

Requires PHP 7

## Usage

### Layout
src/App/LayoutTemplate.php
```php
declare(strict_types=1);

namespace App;

use Satori\Template\AbstractTemplate;

class LayoutTemplate extends AbstractTemplate
{
    protected $layoutBlock = '/app/layout';

    protected $commonVars = [];
    protected $layoutVars = [];

    protected function init(array $data)
    {
        $company = $this->params['company'];

        $this->commonVars['company'] = $company;

        $this->layoutVars['app_name'] = $company;
        $this->layoutVars['title'] = $company;
        $this->layoutVars['copyright'] = date('Y') . ' ' . $company;
    }
}
```

template/app/layout.php
```php
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= $title ?></title>
        <?= $this->head() ?>
    </head>
    <body>
        <header>
            <div class="logo">
                <a href="/"><?= $app_name ?></a>
            </div>
            <nav>
                <ul>
                    <li>
                        <a href="/news">News</a>
                    </li>
                    <li>
                        <a href="/contacts">Contacts</a>
                    </li>
                    <li>
                        <a href="/about">About</a>
                    </li>
                </ul>
            </nav>
        </header>
        <main>
            <?= $this->inset('content') ?>
        </main>
        <footer>
            <div class="copyright">Copyright &copy; <?= $copyright ?></div>
        </footer>
    </body>
</html>
```

### Page
src/Page/ReadTemplate.php
```php
declare(strict_types=1);

namespace Page;

use App\LayoutTemplate;

class ReadTemplate extends LayoutTemplate
{
    protected $headBlock = '/app/head';
    protected $contentBlock = '/page/read';

    protected $headVars = [];
    protected $contentVars = [];

    protected function init(array $data)
    {
        parent::init($data);

        $page = $data['page'];

        $this->layoutVars['title'] = $page->title;

        $this->headVars['description'] = $page->description;

        $this->contentVars['page'] = $page;
    }
}
```

template/app/head.php
```php
<?php $_ = 8 ?>
<meta name="description" content="<?= $description ?>">
```

template/page/read.php
```php
<?php $_ = 12 ?>
<article class="content">
    <h1><?= $page->name ?></h1>
    <div>
        <?= $page->content ?>
    </div>
</article>
```

### Render page
index.php
```php
declare(strict_types=1);

use Page\ReadTemplate;

$page = new stdClass();
$page->title = 'About';
$page->description = 'About company';
$page->name = 'About us';
$page->content = 'The company is the market leader';

$template = new ReadTemplate(
    '/template',
    ['company' => 'My company']
);

echo $template->render(['page' => $page]);
```

## License
MIT License
