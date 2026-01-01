<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Models (ORM)</h1>
            <p class="text-lg text-secondary mb-8">Active Record ORM for database interactions</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Creating Models</h2>
                <pre class="code-block">namespace App\Models;

use FF\Database\Model;

class User extends Model
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'name', 'email', 'password'
    ];
    
    protected array $hidden = [
        'password'
    ];
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">CRUD Operations</h2>
                
                <h3 class="text-xl font-semibold mb-3 mt-6">Create</h3>
                <pre class="code-block">$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">Find</h3>
                <pre class="code-block">$user = User::find(1);
$users = User::all();
$user = User::where('email', 'john@example.com')->first();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">Update</h3>
                <pre class="code-block">$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">Delete</h3>
                <pre class="code-block">$user = User::find(1);
$user->delete();</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Relationships</h2>
                
                <h3 class="text-xl font-semibold mb-3 mt-6">Has Many</h3>
                <pre class="code-block">class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}

// Usage
$user = User::find(1);
$posts = $user->posts();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">Belongs To</h3>
                <pre class="code-block">class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

// Usage
$post = Post::find(1);
$author = $post->user();</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/database" class="btn btn-secondary">← Previous: Database</a>
                <a href="/docs/validation" class="btn btn-primary">Next: Validation →</a>
            </div>
        </div>
    </div>
</div>
