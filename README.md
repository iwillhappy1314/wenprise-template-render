# wenprise-template-loader

Allow WordPress plugin user to overwrite plugin templates in themes

## args
```php
new TemplateHelper($theme_path, $plugin_path)
```

- $theme_path: [string] The theme directory name containing template files used to override plugin templates
- $plugin_path: [string] The absolute directory containing template in plugins.

## Useage

```php
$loader = new TemplateHelper('wenprise-term-list-block', WENPRISE_TERM_LIST_BLOCK_PATH . 'templates');
$loader->get_template('list.php', compact(['taxonomy', 'terms']));
```