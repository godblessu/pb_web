<?php
namespace Zhiyi\Component\ZhiyiPlus\PlusComponentPc\ViewComposers;

use Illuminate\View\View;
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\createRequest;

class QuestionRelevant
{
    public function compose(View $view)
    {
        $issues = createRequest('GET', '/api/v2/questions', ['limit' => 10]);

        $view->with('issues', $issues);
    }
}