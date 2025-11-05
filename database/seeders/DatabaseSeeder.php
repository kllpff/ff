<?php

use FF\Framework\Database\Seeder;
use FF\Framework\Database\Connection;
use FF\Framework\Security\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample users
        $connection = new Connection();
        
        // Insert users
        $connection->table('users')->insert([
            ['name' => 'John Doe', 'email' => 'john@example.com', 'password' => Hash::make('password123'), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => Hash::make('password123'), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ]);

        // Insert blog categories
        $connection->table('blog_categories')->insert([
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Latest tech news and tutorials', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Web Development', 'slug' => 'web-development', 'description' => 'Web dev tips and tricks', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'PHP', 'slug' => 'php', 'description' => 'PHP programming guides', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ]);

        // Insert blog posts
        $connection->table('blog_posts')->insert([
            [
                'title' => 'Getting Started with FF Framework',
                'slug' => 'getting-started-ff-framework',
                'content' => 'FF Framework is a modern PHP MVC framework designed for building web applications quickly and efficiently. In this guide, we\'ll explore the core features and get you started in minutes.',
                'category_id' => 1,
                'user_id' => 1,
                'views' => 42,
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'title' => 'Building RESTful APIs with FF',
                'slug' => 'building-restful-apis',
                'content' => 'Learn how to build powerful RESTful APIs using the FF Framework. We\'ll cover routing, middleware, authentication, and more.',
                'category_id' => 2,
                'user_id' => 2,
                'views' => 28,
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'title' => 'PHP Best Practices Guide',
                'slug' => 'php-best-practices',
                'content' => 'Discover essential PHP best practices for writing clean, maintainable code. From coding standards to security, we cover it all.',
                'category_id' => 3,
                'user_id' => 1,
                'views' => 156,
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'title' => 'Database Design Tips',
                'slug' => 'database-design-tips',
                'content' => 'Expert tips for designing efficient and scalable databases. Learn about normalization, indexing, and query optimization.',
                'category_id' => 1,
                'user_id' => 2,
                'views' => 89,
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
        ]);

        // Insert sample comments
        $connection->table('blog_comments')->insert([
            [
                'content' => 'Great tutorial! Really helped me understand the framework.',
                'post_id' => 1,
                'user_id' => 2,
                'author_name' => 'Jane Smith',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            ],
            [
                'content' => 'Can you do a deeper dive into authentication?',
                'post_id' => 2,
                'user_id' => null,
                'author_name' => 'Guest User',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'content' => 'Excellent guide! This should be in every PHP developer\'s reading list.',
                'post_id' => 3,
                'user_id' => 1,
                'author_name' => 'John Doe',
                'created_at' => date('Y-m-d H:i:s', strtotime('-12 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-12 hours')),
            ],
        ]);

        echo "✅ Database seeded successfully!\n";
    }
}
