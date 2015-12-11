# Shortcode standalone class

Really simple shortcode parser without regex

* without regex
* nested shortcodes with unlimited level
* with aliases
* with the ability to show shortcode instead of compile it
* with predefined arguments

## Example

```php
$sample = '
[shortcode]
[short argument="value"]
[shortcode active other="complex value"]
[short]content[/short]
[shortcode argument="value"]content[/shortcode]

[span6]
    [span6]
        [span6   ]Lorem[/span6]
        [span4]
    [/span6]
    [span4]
        [span6 class="first" ]dolor[/span6]
        [span6  class="last" ]cudere[/span6]
    [/span4]
[/span6]

[span6]
    @[span6]
        [span6]Lorem[/span6]
    [/span6]
[/span6]
';

include 'path/to/Shortcode.php';

$sc = artygrand\Shortcode::instance();

$sc ->add('shortcode', 'shortcode')
    ->withAlias('short')
    ->add('span4', array(
        'grid',
        array(
            'cols' => 4
    )))
    ->add('span6', array(
        'grid',
        array(
            'cols' => 6
    )))
    ->withAlias('col6');

echo $sample . "\n<hr>\n";
echo $sc->compile($sample);

// functions
function shortcode($args, $content = null){
    if (is_null($content)){
        return print_r($args, true);
    }
    $args['content'] = $content;
    return print_r($args, true);
}

function grid($args, $content = null){
    $args = array_merge(array(
        'cols' => 1,
        'class' => '',
    ), $args);

    $class = $args['class'] ? ' class="' . $args['class'] . '"' : '';

    if (is_null($content)){
        return '<div style="width:' . ($args['cols']/0.12) .'%;"' . $class . '>NoContent</div>';
    }
    return '<div style="width:' . ($args['cols']/0.12) .'%;"' . $class . '>' . $content . '</div>';
}
```

### End result will be like

```
Array
(
)

Array
(
    [argument] => value
)

Array
(
    [active] => 1
    [other] => complex value
)

Array
(
    [content] => content
)

Array
(
    [argument] => value
    [content] => content
)


<div style="width:50%;">
    <div style="width:50%;">
        <div style="width:50%;">Lorem</div>
        <div style="width:33.333333333333%;">NoContent</div>
    </div>
    <div style="width:33.333333333333%;">
        <div style="width:50%;" class="first">dolor</div>
        <div style="width:50%;" class="last">cudere</div>
    </div>
</div>

<div style="width:50%;">
    [span6]
        <div style="width:50%;">Lorem</div>
    [/span6]
</div>
```

## License

Released under the MIT License - see `LICENSE.txt` for details.
