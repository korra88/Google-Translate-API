### Google Translate library

Improvement made to [https://github.com/viniciusgava/Google-Translate-API](iniciusgava's Google-Translate-API) library.

Please note that to use Google Translate APIs you have to activate billing in your project and use an API key (these are paid APIs!).

#### Usage

Translation:

```
require_once '/path/to/GoogleTranslate.php';

try {
    $GT = new GoogleTranslate('YOUR_VALID_API_KEY');

    // Galli Caesaris saevitia @ http://www.thelatinlibrary.com/ammianus/14.shtml
    $text = 'Post emensos insuperabilis expeditionis eventus languentibus partium animis, quas periculorum varietas fregerat et laborum, nondum tubarum cessante clangore vel milite locato per stationes hibernas, fortunae saevientis procellae tempestates alias rebus infudere communibus per multa illa et dira facinora Caesaris Galli, qui ex squalore imo miseriarum in aetatis adultae primitiis ad principale culmen insperato saltu provectus ultra terminos potestatis delatae procurrens asperitate nimia cuncta foedabat. Propinquitate enim regiae stirpis gentilitateque etiam tum Constantini nominis efferebatur in fastus, si plus valuisset, ausurus hostilia in auctorem suae felicitatis, ut videbatur.';
    
    // Translate latin to english
    $translateTo = 'en';
    $translateFrom = 'la'; // If not set or null, will be set to the decoded languages during translations
    // Translate latin to english
    $translated_text = $GT->translate($text, $translateTo, $translateFrom);
    
    echo "Galli Caesaris saevitia<br/>" . $translated_text;

} catch (GoogleTranslateException $e) {
    echo "Error: " . $e->message;
}

```

#### Notes

The library doesn't check if the language is supported but that the language code is well formatted, for the complete list of supported languages
visits [https://cloud.google.com/translate/v2/using_rest#language-params](Google Translate API docs).

Correctly switches to POST request when query url is longer than 2K characters.
