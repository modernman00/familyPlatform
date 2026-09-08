<?php
namespace App\controller\register;

use App\controller\Base;
use Src\Select;

final class Search extends Base
{
    public function index(): void
    {
        try {
            
            $hint =  checkInput($_REQUEST['hint']);
            $hint = is_string($hint) ? $hint : '';
            $attribute = checkInput($_GET['attribute']);
            $attribute = is_string($attribute) ? $attribute : '';
            $subject = checkInput($_GET['subject']);
            $subject = is_string($subject) ? $subject : '';

            // Only ever reflect a known attribute name back into markup.
            $allowedSubjects = ['father', 'mother', 'spouse', 'sibling', 'child', 'kid'];
            if (!in_array(strtolower($subject), $allowedSubjects, true)) {
                $subject = 'relative';
            }
            $subjectSafe = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');

            $hint = strtolower($hint);
            $msg1 = "Good news! your {$subjectSafe} is already registered on the platform";
            $msg2 = "<h4><i>Your {$subjectSafe} is not on the platform. Do you want us to send them a text to register to the platform</i>? </h4>" . $this->checkBox($subject);

            $query = Select::formAndMatchQuery(selection:"SELECT_COUNT_ONE", table:'contact', identifier1: $attribute);
            $outcome = Select::selectFn2($query, [$hint]);

            $result = (!$outcome) ? $msg2 : $msg1;
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- the only reflected value ($subject) is restricted to a fixed allow-list of relation names and htmlspecialchars-encoded above; checkBox() further strips it to [A-Za-z0-9_].
            echo $result;
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function checkBox(string $subject): string
    {
        // Callers pass an allow-listed subject; still restrict to word chars so
        // the value can never break out of the id/name/for attributes.
        $s = preg_replace('/[^A-Za-z0-9_]/', '', $subject) ?: 'relative';

        return "<div class='form-check form-check-inline'>
            <input class='form-check-input' type='radio' id='{$s}Yes' name='{$s}Checkbox' value='Yes'>
            <label class='form-check-label' for='{$s}Yes'>Yes</label>
            </div>
            <div class='form-check form-check-inline'>
            <input class='form-check-input' type='radio' id='{$s}No' name='{$s}Checkbox' value='No'>
            <label class='form-check-label' for='{$s}No'>No</label>
            </div>
           ";
    }
}
