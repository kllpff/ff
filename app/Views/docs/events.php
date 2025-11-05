<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Events</h1>
            <p class="text-lg text-secondary mb-8">Event-driven architecture for decoupled code</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Creating Events</h2>
                <pre class="code-block">namespace App\Events;

use FF\Framework\Events\Event;

class UserRegistered extends Event
{
    public function __construct(
        public User $user
    ) {}
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Creating Listeners</h2>
                <pre class="code-block">namespace App\Listeners;

use App\Events\UserRegistered;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
    {
        // Send email to $event->user
    }
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Registering Events</h2>
                <p class="text-secondary mb-3">In <code class="code-inline">config/events.php</code>:</p>
                <pre class="code-block">return [
    UserRegistered::class => [
        SendWelcomeEmail::class,
        LogUserRegistration::class,
    ],
];</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Dispatching Events</h2>
                <pre class="code-block">use FF\Framework\Events\EventDispatcher;

$dispatcher = app(EventDispatcher::class);
$dispatcher->dispatch(new UserRegistered($user));</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/logging" class="btn btn-secondary">← Previous: Logging</a>
                <a href="/docs/security" class="btn btn-primary">Next: Security →</a>
            </div>
        </div>
    </div>
</div>
