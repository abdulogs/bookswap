<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\BookRequest;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      // Create sample users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@bookswap.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        $user3 = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        // Create sample books
        $books = [
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'genre' => 'Fiction',
                'condition' => 'Excellent',
                'status' => 'Available',
                'description' => 'A classic American novel about the Jazz Age.',
                'location' => 'New York, NY',
                'user_id' => $user1->id,
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'genre' => 'Fiction',
                'condition' => 'Good',
                'status' => 'Available',
                'description' => 'A powerful story about racial injustice in the American South.',
                'location' => 'New York, NY',
                'user_id' => $user1->id,
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'genre' => 'Science Fiction',
                'condition' => 'Good',
                'status' => 'Available',
                'description' => 'A dystopian novel about totalitarian surveillance.',
                'location' => 'Los Angeles, CA',
                'user_id' => $user2->id,
            ],
            [
                'title' => 'Pride and Prejudice',
                'author' => 'Jane Austen',
                'genre' => 'Romance',
                'condition' => 'Excellent',
                'status' => 'Available',
                'description' => 'A romantic novel of manners.',
                'location' => 'Los Angeles, CA',
                'user_id' => $user2->id,
            ],
            [
                'title' => 'The Hobbit',
                'author' => 'J.R.R. Tolkien',
                'genre' => 'Fantasy',
                'condition' => 'Fair',
                'status' => 'Available',
                'description' => 'An adventure fantasy novel about a hobbit\'s journey.',
                'location' => 'Chicago, IL',
                'user_id' => $user3->id,
            ],
            [
                'title' => 'The Art of War',
                'author' => 'Sun Tzu',
                'genre' => 'Philosophy',
                'condition' => 'Good',
                'status' => 'Available',
                'description' => 'Ancient Chinese text on military strategy.',
                'location' => 'Chicago, IL',
                'user_id' => $user3->id,
            ],
            [
                'title' => 'Sapiens',
                'author' => 'Yuval Noah Harari',
                'genre' => 'Non-Fiction',
                'condition' => 'Excellent',
                'status' => 'Available',
                'description' => 'A brief history of humankind.',
                'location' => 'New York, NY',
                'user_id' => $user1->id,
            ],
            [
                'title' => 'The Alchemist',
                'author' => 'Paulo Coelho',
                'genre' => 'Fiction',
                'condition' => 'Good',
                'status' => 'Available',
                'description' => 'A novel about following your dreams.',
                'location' => 'Los Angeles, CA',
                'user_id' => $user2->id,
            ],
        ];

        $createdBooks = [];
        foreach ($books as $bookData) {
            $createdBooks[] = Book::create($bookData);
        }

        // Create sample book requests (for demonstration)
        if (count($createdBooks) >= 2) {
            BookRequest::create([
                'book_id' => $createdBooks[0]->id,
                'borrower_id' => $user2->id,
                'owner_id' => $user1->id,
                'request_type' => 'borrow',
                'status' => 'Pending',
                'message' => 'I would love to read this classic!',
            ]);

            BookRequest::create([
                'book_id' => $createdBooks[2]->id,
                'borrower_id' => $user3->id,
                'owner_id' => $user2->id,
                'request_type' => 'swap',
                'swap_book_id' => $createdBooks[4]->id,
                'status' => 'Approved',
                'message' => 'Would like to swap with my Hobbit book.',
                'borrowed_at' => now()->subDays(5),
                'due_date' => now()->addDays(9),
            ]);
        }
    }
}
