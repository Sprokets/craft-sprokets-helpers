# sprokets-helpers plugin for Craft CMS 4.x

## Requirements

This plugin requires Craft CMS 4.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1.  Open your terminal and go to your Craft project:

        cd /path/to/project

2.  Then tell Composer to load the plugin:

        composer require craft-sprokets-helpers/sprokets-helpers

3.  In the Control Panel, go to Settings → Plugins and click the “Install” button for sprokets-helpers.

## Sprokets Helpers Overview

Just some small things to help ease Craft development

## Admin Improvements:

- Add expended options in left-hand menu
- Auto-Expand asset folders

## Twig Extensions:

### Filters:

#### `idString`

Use an entry title or anything else as an html/array id.

Usage:

```twig
{% set str = 'Hello 123 - #42' %}
{{str|idString}}
{# outputs 'Hello12342' #}
```

#### `nl2p`

Convert plain-text fields with line breaks into html. Changes a single line break to `<br>` and double line breaks to `<p>`

Should probably be used with the `raw` filter.

Usage:

```twig
{% set str %}
test
test1

test2
{% endset %}
{{str|nl2p}}
{# outputs <p>test<br>test1</p><p>test2</p> #}
```

#### `emailObfuscate`

Link and obfuscate plain-text emails in any chunk of text.

Useage:

```twig
{{entry.body|emailObfuscate}}
```

OR

```twig
{% filter emailObfuscate %}
  {# Any Content #}
{% endfilter %}
```

This can be used to over entire chunks of a website, for instance in your layout template:

```twig
{% filter emailObfuscate %}
  {% block content %}{% endblock %}
{% endfilter %}
```

### Functions

#### `getVideoInfo`

Retrieves video info from youtube or vimeo based on a video url.

Usage:

```twig
{% set info = getVideoInfo('https://www.youtube.com/watch?v=ue80QwXMRHg') %}
{{dump(info)}}
{#
  array (size=5)
    'id' => string 'ue80QwXMRHg' (length=11)
    'type' => string 'youtube' (length=7)
    'embed' => string '<iframe src="https://www.youtube.com/embed/ue80QwXMRHg?rel=0&showinfo=0&color=white&iv_load_policy=3" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' (length=184)
    'hqthumbnail' => string 'https://i1.ytimg.com/vi/ue80QwXMRHg/hqdefault.jpg' (length=49)
    'thumbnail' => string 'https://i1.ytimg.com/vi/ue80QwXMRHg/default.jpg' (length=47)
#}
```
