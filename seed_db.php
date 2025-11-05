<?php

/**
 * Seed database with sample data
 */

require __DIR__ . '/public/index.php';

$connection = \FF\Framework\Database\Model::getConnection();
$pdo = $connection->getPDO();

echo "Seeding database...\n";

try {
    // Insert users
    $pdo->exec("
        INSERT INTO users (name, email, password, created_at, updated_at) VALUES
        ('John Doe', 'john@example.com', '" . password_hash('password123', PASSWORD_BCRYPT) . "', NOW(), NOW()),
        ('Jane Smith', 'jane@example.com', '" . password_hash('password123', PASSWORD_BCRYPT) . "', NOW(), NOW())
    ");
    echo "✓ Inserted 2 users\n";

    // Insert blog categories
    $pdo->exec("
        INSERT INTO blog_categories (name, slug, description, created_at, updated_at) VALUES
        ('Technology', 'technology', 'Latest tech news and tutorials', NOW(), NOW()),
        ('Web Development', 'web-development', 'Web dev tips and tricks', NOW(), NOW()),
        ('PHP', 'php', 'PHP programming guides', NOW(), NOW())
    ");
    echo "✓ Inserted 3 categories\n";

    // Insert blog posts
    $pdo->exec("
        INSERT INTO blog_posts (title, slug, content, category_id, user_id, views, status, created_at, updated_at) VALUES
        ('Getting Started with FF Framework', 'getting-started-ff-framework', 'FF Framework is a modern PHP MVC framework designed for building web applications quickly and efficiently. In this guide, we will explore the core features and get you started in minutes.', 1, 1, 42, 'published', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
        ('Building RESTful APIs with FF', 'building-restful-apis', 'Learn how to build powerful RESTful APIs using the FF Framework. We will cover routing, middleware, authentication, and more.', 2, 2, 28, 'published', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
        ('PHP Best Practices Guide', 'php-best-practices', 'Discover essential PHP best practices for writing clean, maintainable code. From coding standards to security, we cover it all.', 3, 1, 156, 'published', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
        ('Database Design Tips', 'database-design-tips', 'Expert tips for designing efficient and scalable databases. Learn about normalization, indexing, and query optimization.', 1, 2, 89, 'published', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY))
    ");
    echo "✓ Inserted 4 posts\n";

    // Insert comments
    $pdo->exec("
        INSERT INTO blog_comments (content, post_id, user_id, author_name, created_at, updated_at) VALUES
        ('Great tutorial! Really helped me understand the framework.', 1, 2, 'Jane Smith', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
        ('Can you do a deeper dive into authentication?', 2, NULL, 'Guest User', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
        ('Excellent guide! This should be in every PHP developers reading list.', 3, 1, 'John Doe', DATE_SUB(NOW(), INTERVAL 12 HOUR), DATE_SUB(NOW(), INTERVAL 12 HOUR))
    ");
    echo "✓ Inserted 3 comments\n";

    echo "\n✅ Database seeded successfully!\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
