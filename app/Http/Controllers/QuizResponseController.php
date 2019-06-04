<?php

namespace App\Http\Controllers;

use Auth;
use App\Quiz;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\SubmitQuizRequest;
use Symfony\Component\HttpFoundation\Response;

class QuizResponseController extends Controller
{
    public function store(SubmitQuizRequest $request, Quiz $quiz)
    {
        $data = $request->validated();

        $team = $quiz->event->participatingTeamByUser(Auth::user());

        $team->endQuiz($quiz, $data['responses'] ?? []);

        if ($quiz->isTimeLimitExceeded($team)) {
            flash('Your time limit exceeded!')->error();
            $status = Response::HTTP_REQUEST_TIMEOUT;
        } else {
            flash('Your response has been recorded! All The Best!')->success();
            $status = Response::HTTP_ACCEPTED;
        }

        return $this->getJsonOrRedirect($status);
    }

    private function getJsonOrRedirect($status = 202)
    {
        if (! request()->expectsJson()) {
            return redirect()->back();
        }

        return response()->json([
            'message' => Session::pull('flash_notification')->toArray()[0],
        ], $status);
    }
}
