<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Quiz;
use App\Event;
use Illuminate\Support\Facades\Session;

class TeamTakesQuizTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function non_participating_teams_are_redirected_to_dashboard_with_appropriate_error_message()
    {
        $users = create(User::class, 2);
        $team = $users[0]->createTeam('Team', $users[1]);
        $events = create(Event::class,2);
        $team->participate($events[0]);
        $quiz = create(Quiz::class, 1, ['event_id' => $events[1]->id]);

        $this->withoutExceptionHandling()->be($users[0]);

        $response = $this->get(route('quizzes.take', $quiz));

        $response->assertRedirect(route('dashboard'))->assertSessionHas('flash_notification');
        $this->assertEquals('danger', Session::get('flash_notification')->first()->level);
        $this->assertContains('not participating', Session::get('flash_notification')->first()->message);
    }

    /** @test */
    public function participating_teams_are_redirected_with_error_if_quiz_is_not_active()
    {
        $users = create(User::class, 2);
        $team = $users[0]->createTeam('Team', $users[1]);
        $event = create(Event::class);
        $quiz = create(Quiz::class, 1, ['event_id' => $event->id]);
        $team->participate($event);

        $this->withoutExceptionHandling()->be($users[0]);

        $response = $this->get(route('quizzes.take', $quiz));

        $response->assertRedirect(route('dashboard'))->assertSessionHas('flash_notification');
        
        $this->assertEquals('danger', Session::get('flash_notification')->first()->level);
    }

    /** @test */
    public function participating_teams_are_cannot_take_active_quiz_if_they_are_not_yet_a_quiz_participant()
    {
        $users = create(User::class, 2);
        $team = $users[0]->createTeam('Team', $users[1]);
        $event = create(Event::class);
        $team->participate($event);
        $quiz = create(Quiz::class, 1, ['event_id' => $event->id]);
        $quiz->setActive();

        $this->withoutExceptionHandling()->be($users[0]);

        $response = $this->get(route('quizzes.take', $quiz));

        $response->assertRedirect(route('dashboard'))->assertSessionHas('flash_notification');
        $this->assertEquals('danger', Session::get('flash_notification')->first()->level);
        $this->assertContains('not allowed', Session::get('flash_notification')->first()->message);
    }

    
}
