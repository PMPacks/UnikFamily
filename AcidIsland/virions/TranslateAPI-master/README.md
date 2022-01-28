# TranslateAPI
Library translator for Pocketmine-MP

# Example

Translate: 
```php
use phuongaz\Translate\Translate;

$source = "vi";
$target = "en";
$text = "Xin chào";

$result = Translate::translate($source, $target, $text);

return $result; // "Hello"
```
Detect Language:

```php
use phuongaz\Translate\Translate;

$text = "Hello";

$result = Translate::detect($text);

return $result; // "en"
```
