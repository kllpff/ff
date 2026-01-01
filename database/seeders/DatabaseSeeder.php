<?php

use FF\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Post;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        echo "🌱 Seeding database...\n";

        // Create sample users (idempotent)
        echo "  → Creating users...\n";
        // Admin user (ensure verified)
        $user1 = User::where('email', '=', 'admin@example.com')->first();
        if ($user1) {
            $user1->update([
                'name' => 'Admin User',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'is_admin' => 1,
            ]);
            $user1->markEmailAsVerified();
        } else {
            $user1 = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'is_admin' => 1,
            ]);
            $user1->markEmailAsVerified();
        }

        // John Doe
        $user2 = User::where('email', '=', 'john@example.com')->first();
        if ($user2) {
            $user2->update([
                'name' => 'John Doe',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'is_admin' => 0,
            ]);
        } else {
            $user2 = User::create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'is_admin' => 0,
            ]);
        }

        // Jane Smith
        $user3 = User::where('email', '=', 'jane@example.com')->first();
        if ($user3) {
            $user3->update([
                'name' => 'Jane Smith',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'is_admin' => 0,
            ]);
        } else {
            $user3 = User::create([
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'is_admin' => 0,
            ]);
        }

        // Create categories
        echo "  → Creating categories...\n";
        $tech = Category::where('slug', '=', 'technology')->first();
        if (!$tech) {
            $tech = Category::create([
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Latest tech news and tutorials',
            ]);
        }

        $webdev = Category::where('slug', '=', 'web-development')->first();
        if (!$webdev) {
            $webdev = Category::create([
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Web dev tips and tricks',
            ]);
        }

        $php = Category::where('slug', '=', 'php')->first();
        if (!$php) {
            $php = Category::create([
                'name' => 'PHP',
                'slug' => 'php',
                'description' => 'PHP programming guides',
            ]);
        }

        // Create blog posts
        echo "  → Creating posts...\n";
        if (!Post::where('slug', '=', 'getting-started-ff-framework')->first()) Post::create([
            'title' => 'Getting Started with FF Framework',
            'slug' => 'getting-started-ff-framework',
            'excerpt' => 'A comprehensive guide to getting started with FF Framework in minutes.',
            'content' => 'FF Framework is a modern PHP MVC framework designed for building web applications quickly and efficiently. In this guide, we\'ll explore the core features and get you started in minutes.',
            'category_id' => $tech->id,
            'user_id' => $user1->id,
            'views' => 42,
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
        ]);

        if (!Post::where('slug', '=', 'building-restful-apis')->first()) Post::create([
            'title' => 'Building RESTful APIs with FF',
            'slug' => 'building-restful-apis',
            'excerpt' => 'Learn how to build powerful RESTful APIs using the FF Framework.',
            'content' => 'Learn how to build powerful RESTful APIs using the FF Framework. We\'ll cover routing, middleware, authentication, and more.',
            'category_id' => $webdev->id,
            'user_id' => $user2->id,
            'views' => 28,
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
        ]);

        if (!Post::where('slug', '=', 'php-best-practices')->first()) Post::create([
            'title' => 'PHP Best Practices Guide',
            'slug' => 'php-best-practices',
            'excerpt' => 'Essential PHP best practices for writing clean, maintainable code.',
            'content' => 'Discover essential PHP best practices for writing clean, maintainable code. From coding standards to security, we cover it all.',
            'category_id' => $php->id,
            'user_id' => $user1->id,
            'views' => 156,
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);

        if (!Post::where('slug', '=', 'database-design-tips')->first()) Post::create([
            'title' => 'Database Design Tips',
            'slug' => 'database-design-tips',
            'excerpt' => 'Expert tips for designing efficient and scalable databases.',
            'content' => 'Expert tips for designing efficient and scalable databases. Learn about normalization, indexing, and query optimization.',
            'category_id' => $tech->id,
            'user_id' => $user3->id,
            'views' => 89,
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ]);

        if (!Post::where('slug', '=', 'understanding-mvc-pattern')->first()) Post::create([
            'title' => 'Understanding MVC Pattern',
            'slug' => 'understanding-mvc-pattern',
            'excerpt' => 'A deep dive into the Model-View-Controller architectural pattern.',
            'content' => 'The MVC pattern is fundamental to modern web development. This article breaks down each component and shows how they work together.',
            'category_id' => $webdev->id,
            'user_id' => $user2->id,
            'views' => 12,
            'status' => 'draft',
            'published_at' => null,
        ]);

        echo "✅ Database seeded successfully!\n";
        echo "   Users: 3 (admin@example.com / password123)\n";
        echo "   Categories: 3\n";
        echo "   Posts: 5 (4 published, 1 draft)\n";
    }
}
