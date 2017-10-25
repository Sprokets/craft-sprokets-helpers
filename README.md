# Sprokets Helpers plugin for Craft CMS

## Installation

To install Sprokets Helpers, follow these steps:

1. Download & unzip the file and place the `sproketshelpers` directory into your `craft/plugins` directory
2. -OR- do a `git clone ???` directly into your `craft/plugins` folder. You can then update it with `git pull`
3. -OR- install with Composer via `composer require /sproketshelpers`
4. Install plugin in the Craft Control Panel under Settings > Plugins

Sprokets Helpers works on Craft 2.4.x and Craft 2.5.x.

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

####`nl2p`

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