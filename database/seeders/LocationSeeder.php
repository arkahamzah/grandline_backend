<?php
// database/seeders/LocationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            // Manga Stores
            [
                'name' => 'Kinokuniya Bookstore',
                'description' => 'Leading Japanese bookstore with extensive manga collection. Features latest releases and classic series.',
                'category' => 'Manga Store',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'address' => 'Plaza Senayan, Level 3, Jl. Asia Afrika No.8, Gelora, Jakarta',
                'phone_number' => '+62-21-5793-4555',
                'website' => 'https://indonesia.kinokuniya.com',
                'opening_hours' => 'Mon-Sun: 10:00 AM - 10:00 PM',
                'rating' => 4.5
            ],
            [
                'name' => 'Gramedia Grand Indonesia',
                'description' => 'Large bookstore chain with dedicated manga and light novel section. Great for finding Indonesian translated manga.',
                'category' => 'Manga Store',
                'latitude' => -6.195556,
                'longitude' => 106.820833,
                'address' => 'Grand Indonesia Shopping Town, West Mall Level 3A, Jakarta',
                'phone_number' => '+62-21-2358-3162',
                'website' => 'https://www.gramedia.com',
                'opening_hours' => 'Mon-Sun: 10:00 AM - 10:00 PM',
                'rating' => 4.2
            ],
            [
                'name' => 'Manga Corner Bandung',
                'description' => 'Specialized manga store with rare and collectible series. Popular among serious manga collectors.',
                'category' => 'Manga Store',
                'latitude' => -6.917464,
                'longitude' => 107.619125,
                'address' => 'Jl. Dago No.56, Coblong, Bandung',
                'phone_number' => '+62-22-2503-1234',
                'opening_hours' => 'Mon-Sat: 9:00 AM - 9:00 PM, Sun: 10:00 AM - 8:00 PM',
                'rating' => 4.7
            ],
            [
                'name' => 'Otaku Store Surabaya',
                'description' => 'Complete otaku destination with manga, anime merchandise, and gaming accessories.',
                'category' => 'Manga Store',
                'latitude' => -7.257472,
                'longitude' => 112.752090,
                'address' => 'Jl. Pemuda No.31-37, Embong Kaliasin, Surabaya',
                'phone_number' => '+62-31-5326-7890',
                'opening_hours' => 'Mon-Sun: 10:00 AM - 9:00 PM',
                'rating' => 4.3
            ],

            // Anime Cafes
            [
                'name' => 'Akiba Cafe Jakarta',
                'description' => 'Anime-themed cafe with manga reading corner, cosplay events, and Japanese cuisine.',
                'category' => 'Anime Cafe',
                'latitude' => -6.175110,
                'longitude' => 106.827153,
                'address' => 'Jl. Sabang No.20, Menteng, Jakarta Pusat',
                'phone_number' => '+62-21-3190-1234',
                'website' => 'https://akibacafe.id',
                'opening_hours' => 'Mon-Sun: 11:00 AM - 11:00 PM',
                'rating' => 4.4
            ],
            [
                'name' => 'Maid Cafe Shibuya',
                'description' => 'Japanese-style maid cafe with anime atmosphere, themed drinks, and manga library.',
                'category' => 'Anime Cafe',
                'latitude' => -6.220000,
                'longitude' => 106.845000,
                'address' => 'Jl. Panglima Polim VIII No.10, Kebayoran Baru, Jakarta',
                'phone_number' => '+62-21-7221-5678',
                'opening_hours' => 'Tue-Sun: 12:00 PM - 10:00 PM, Closed Monday',
                'rating' => 4.1
            ],
            [
                'name' => 'Otaku Lounge Bandung',
                'description' => 'Cozy cafe for anime and manga fans with gaming stations and weekly anime screening.',
                'category' => 'Anime Cafe',
                'latitude' => -6.902395,
                'longitude' => 107.618814,
                'address' => 'Jl. Riau No.67, Citarum, Bandung',
                'phone_number' => '+62-22-4203-9876',
                'opening_hours' => 'Mon-Sun: 10:00 AM - 10:00 PM',
                'rating' => 4.6
            ],

            // Conventions
            [
                'name' => 'Indonesia Comic Con',
                'description' => 'Annual pop culture convention featuring manga, anime, gaming, and cosplay competitions.',
                'category' => 'Convention',
                'latitude' => -6.225014,
                'longitude' => 106.799531,
                'address' => 'Jakarta Convention Center, Jl. Gatot Subroto, Jakarta',
                'phone_number' => '+62-21-2927-9999',
                'website' => 'https://indonesiacomiccon.com',
                'opening_hours' => 'Event dates vary (Usually September)',
                'rating' => 4.8
            ],
            [
                'name' => 'Bandung Creative Festival',
                'description' => 'Creative arts festival with strong manga and anime community presence.',
                'category' => 'Convention',
                'latitude' => -6.914744,
                'longitude' => 107.609810,
                'address' => 'Trans Studio Bandung, Jl. Gatot Subroto No.289, Bandung',
                'phone_number' => '+62-22-8782-0000',
                'opening_hours' => 'Event dates vary (Usually July)',
                'rating' => 4.5
            ],
            [
                'name' => 'Surabaya Pop Culture Festival',
                'description' => 'East Java largest pop culture event with manga artist meet & greet.',
                'category' => 'Convention',
                'latitude' => -7.275167,
                'longitude' => 112.734398,
                'address' => 'Grand City Mall, Jl. Mayjen Sungkono No.89, Surabaya',
                'phone_number' => '+62-31-5937-1111',
                'opening_hours' => 'Event dates vary (Usually November)',
                'rating' => 4.3
            ],

            // Libraries
            [
                'name' => 'National Library of Indonesia',
                'description' => 'National library with comprehensive manga and graphic novel collection including research materials.',
                'category' => 'Library',
                'latitude' => -6.224982,
                'longitude' => 106.797396,
                'address' => 'Jl. Medan Merdeka Selatan No.11, Gambir, Jakarta',
                'phone_number' => '+62-21-3441-5573',
                'website' => 'https://www.perpusnas.go.id',
                'opening_hours' => 'Mon-Fri: 9:00 AM - 4:00 PM, Sat: 9:00 AM - 3:00 PM',
                'rating' => 4.2
            ],
            [
                'name' => 'Bandung Digital Library',
                'description' => 'Modern library with digital manga collection and manga reading events.',
                'category' => 'Library',
                'latitude' => -6.914864,
                'longitude' => 107.608238,
                'address' => 'Jl. Asia Afrika No.62, Sumur Bandung, Bandung',
                'phone_number' => '+62-22-4264-4200',
                'opening_hours' => 'Mon-Sat: 8:00 AM - 8:00 PM, Sun: 9:00 AM - 5:00 PM',
                'rating' => 4.4
            ],
            [
                'name' => 'Surabaya City Library',
                'description' => 'Public library with growing manga and light novel collection in multiple languages.',
                'category' => 'Library',
                'latitude' => -7.263244,
                'longitude' => 112.737329,
                'address' => 'Jl. Menur Pumpungan No.30, Sukolilo, Surabaya',
                'phone_number' => '+62-31-5947-4589',
                'opening_hours' => 'Mon-Sat: 8:00 AM - 7:00 PM, Sun: 9:00 AM - 4:00 PM',
                'rating' => 4.1
            ],

            // Gaming Centers
            [
                'name' => 'Timezone Grand Indonesia',
                'description' => 'Large gaming center with anime-themed games, rhythm games, and manga-inspired arcade machines.',
                'category' => 'Gaming Center',
                'latitude' => -6.195833,
                'longitude' => 106.820000,
                'address' => 'Grand Indonesia Shopping Town, Level 5, Jakarta',
                'phone_number' => '+62-21-2358-1234',
                'website' => 'https://timezone.co.id',
                'opening_hours' => 'Mon-Sun: 10:00 AM - 10:00 PM',
                'rating' => 4.3
            ],
            [
                'name' => 'Game Station Bandung',
                'description' => 'Gaming cafe popular among manga and anime fans with themed tournaments.',
                'category' => 'Gaming Center',
                'latitude' => -6.905977,
                'longitude' => 107.613144,
                'address' => 'Jl. Braga No.99, Sumur Bandung, Bandung',
                'phone_number' => '+62-22-4233-5566',
                'opening_hours' => 'Mon-Sun: 1:00 PM - 12:00 AM',
                'rating' => 4.5
            ],
            [
                'name' => 'Netcafe Otaku Surabaya',
                'description' => 'Internet cafe with manga reading corner and anime streaming available.',
                'category' => 'Gaming Center',
                'latitude' => -7.294417,
                'longitude' => 112.737724,
                'address' => 'Jl. Dr. Ir. H. Soekarno No.201, Mulyorejo, Surabaya',
                'phone_number' => '+62-31-5961-7788',
                'opening_hours' => 'Mon-Sun: 24 Hours',
                'rating' => 4.0
            ],
            [
                'name' => 'Amusement Park Manga Corner',
                'description' => 'Gaming center inside theme park with manga-themed attractions and games.',
                'category' => 'Gaming Center',
                'latitude' => -6.302106,
                'longitude' => 106.652954,
                'address' => 'Dunia Fantasi, Ancol Dreamland, Jakarta Utara',
                'phone_number' => '+62-21-640-1234',
                'opening_hours' => 'Mon-Fri: 2:00 PM - 9:00 PM, Sat-Sun: 10:00 AM - 9:00 PM',
                'rating' => 4.2
            ],

            // Additional unique locations
            [
                'name' => 'Japan Foundation Cultural Center',
                'description' => 'Cultural center promoting Japanese culture with manga exhibitions and workshops.',
                'category' => 'Library',
                'latitude' => -6.235000,
                'longitude' => 106.799444,
                'address' => 'Summitmas I, Floor 2-3, Jl. Jend. Sudirman Kav. 61-62, Jakarta',
                'phone_number' => '+62-21-520-1266',
                'website' => 'https://jfjakarta.or.id',
                'opening_hours' => 'Mon-Fri: 9:00 AM - 5:30 PM, Sat: 9:00 AM - 1:00 PM',
                'rating' => 4.6
            ],
            [
                'name' => 'Harajuku Cafe Yogyakarta',
                'description' => 'Colorful Japanese pop culture themed cafe with manga library and kawaii atmosphere.',
                'category' => 'Anime Cafe',
                'latitude' => -7.795580,
                'longitude' => 110.378307,
                'address' => 'Jl. Malioboro No.56, Sosromenduran, Yogyakarta',
                'phone_number' => '+62-274-566-7890',
                'opening_hours' => 'Mon-Sun: 10:00 AM - 11:00 PM',
                'rating' => 4.4
            ]
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}