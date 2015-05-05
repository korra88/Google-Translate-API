### Google Translate library

Improvement made to [https://github.com/viniciusgava/Google-Translate-API](iniciusgava's Google-Translate-API) library.

Please note that to use Google Translate APIs you have to activate billing in your project (these are paid APIs!) and use an API key.

#### Usage

Translation:

```
require_once '/path/to/GoogleTranslate.php';

try {
    $GT = new GoogleTranslate('YOUR_API_KEY');

    // Galli Caesaris saevitia @ http://www.thelatinlibrary.com/ammianus/14.shtml
    $text = 'Post emensos insuperabilis expeditionis eventus languentibus partium'
            . ' animis, quas periculorum varietas fregerat et laborum...';
    
    // Translate latin to english
    $translateTo = 'en';
    $translateFrom = 'la';
    // If $translateFrom is not set or null, it's value will be set to the
    // decoded languages during translation
    
    $translated_text = $GT->translate($text, $translateTo, $translateFrom);
    
    echo "Galli Caesaris saevitia<br/>" . html_entity_decode($translated_text);

} catch (GoogleTranslateException $e) {
    echo "Error: " . $e->message;
}

```

#### Notes

The library doesn't check if the language is supported but that the language code is well formatted, for the complete list of supported languages
visits [https://cloud.google.com/translate/v2/using_rest#language-params](Google Translate API docs).

Correctly switches to POST request when query url is longer than 2K characters.
