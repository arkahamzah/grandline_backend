<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Series;
use App\Models\Comic;

class SeriesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Attack on Titan Series
        $attackOnTitan = Series::create([
            'title' => 'Attack on Titan',
            'description' => 'Humanity fights against giant titans to survive',
            'cover_image' => 'attackontitan_cover.gif',
            'status' => 'completed'
        ]);

        // Attack on Titan Chapters 01-04 (cover dari page 4)
        for ($i = 1; $i <= 4; $i++) {
            $chapterNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            
            $attackPages = [];
            if ($i == 1) {
                // Chapter 01 menggunakan 00.jpg sampai 23.jpg
                for ($page = 0; $page <= 23; $page++) {
                    $attackPages[] = "attackontitan/Chapter {$chapterNum}/" . str_pad($page, 2, '0', STR_PAD_LEFT) . ".jpg";
                }
                $coverImage = "attackontitan/Chapter {$chapterNum}/04.jpg"; // Page 4 sebagai cover
            } else {
                for ($page = 1; $page <= 25; $page++) {
                    $attackPages[] = "attackontitan/Chapter {$chapterNum}/" . str_pad($page, 2, '0', STR_PAD_LEFT) . ".jpg";
                }
                $coverImage = "attackontitan/Chapter {$chapterNum}/04.jpg"; // Page 4 sebagai cover
            }

            Comic::create([
                'series_id' => $attackOnTitan->id,
                'chapter_number' => $chapterNum,
                'title' => "Chapter {$i}",
                'description' => "Attack on Titan Chapter {$i}",
                'cover_image' => $coverImage,
                'pages' => $attackPages,
                'page_count' => count($attackPages)
            ]);
        }

        // 2. Kage no Jitsuryokusha Series
        $kageNoJitsuryokusha = Series::create([
            'title' => 'Kage no Jitsuryokusha',
            'description' => 'The Eminence in Shadow - A story about a boy who dreams of becoming a shadow broker',
            'cover_image' => 'Kage_no_jitsuryokusha_cover.gif',
            'status' => 'ongoing'
        ]);

        // Kage no Jitsuryokusha Chapters 001-004
        $chapterPageCounts = [
            1 => 39, // Chapter 001: 001.jpg to 039.jpg
            2 => 35, // Chapter 002: estimated
            3 => 32, // Chapter 003: estimated  
            4 => 28, // Chapter 004: estimated
        ];

        for ($i = 1; $i <= 4; $i++) {
            $chapterNum = str_pad($i, 3, '0', STR_PAD_LEFT);
            $pageCount = $chapterPageCounts[$i];
            
            $kagePages = [];
            for ($page = 1; $page <= $pageCount; $page++) {
                $kagePages[] = "Kage_no_jitsuryokusha/Chapter {$chapterNum}/" . str_pad($page, 3, '0', STR_PAD_LEFT) . ".jpg";
            }

            // Chapter titles untuk Kage no Jitsuryokusha
            $chapterTitles = [
                1 => 'I Am Atomic',
                2 => 'The Secret Organization',
                3 => 'Fencing Club',
                4 => 'Shadow Garden',
            ];

            Comic::create([
                'series_id' => $kageNoJitsuryokusha->id,
                'chapter_number' => $chapterNum,
                'title' => $chapterTitles[$i] ?? "Chapter {$i}",
                'description' => "Kage no Jitsuryokusha Chapter {$i}",
                'cover_image' => "Kage_no_jitsuryokusha/Chapter {$chapterNum}/004.jpg", // Page 4 sebagai cover
                'pages' => $kagePages,
                'page_count' => count($kagePages)
            ]);
        }

        // 3. Kaoru Hana wa Rin to Saku Series
        $kaoru = Series::create([
            'title' => 'Kaoru Hana wa Rin to Saku',
            'description' => 'A beautiful story about love and friendship',
            'cover_image' => 'Kaoru Hana wa Rin to Saku_cover.gif',
            'status' => 'ongoing'
        ]);

        // Kaoru Chapters 01-05 (cover dari page 4)
        for ($i = 1; $i <= 5; $i++) {
            $chapterNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            
            $kaoruPages = [];
            for ($page = 1; $page <= 20; $page++) {
                $kaoruPages[] = "Kaoru Hana wa Rin to Saku/Chapter {$chapterNum}/" . str_pad($page, 2, '0', STR_PAD_LEFT) . ".jpg";
            }

            Comic::create([
                'series_id' => $kaoru->id,
                'chapter_number' => $chapterNum,
                'title' => "Chapter {$i}",
                'description' => "Kaoru Hana wa Rin to Saku Chapter {$i}",
                'cover_image' => "Kaoru Hana wa Rin to Saku/Chapter {$chapterNum}/04.jpg", // Page 4 sebagai cover
                'pages' => $kaoruPages,
                'page_count' => count($kaoruPages)
            ]);
        }

        // 4. One Piece Series
        $onePiece = Series::create([
            'title' => 'One Piece',
            'description' => 'The greatest adventure story of pirates searching for One Piece treasure',
            'cover_image' => 'onepiece_cover.gif',
            'status' => 'ongoing'
        ]);

        // One Piece Chapters 001-017 (cover dari page 4)
        for ($i = 1; $i <= 17; $i++) {
            $chapterNum = str_pad($i, 3, '0', STR_PAD_LEFT);
            
            $onePiecePages = [];
            // Estimasi 50 pages per chapter One Piece
            for ($page = 1; $page <= 50; $page++) {
                $onePiecePages[] = "onepiece/Chapter {$chapterNum}/" . str_pad($page, 2, '0', STR_PAD_LEFT) . ".jpg";
            }

            // Title berdasarkan chapter
            $chapterTitles = [
                1 => 'Romance Dawn',
                2 => 'They Call Him "Straw Hat Luffy"',
                3 => 'Introducing "Pirate Hunter" Roronoa Zoro',
                4 => 'The Captain: "Axe-Hand" Morgan',
                5 => 'The King of Pirates and the Master Swordsman',
                6 => 'The First',
                7 => 'Introducing Myself',
                8 => 'Nami',
                9 => 'The Honorable Liar? Captain Usopp',
                10 => 'The Weirdest Guy Ever',
                11 => 'Conspiratorial',
                12 => 'The Darkness Known as Kuro',
                13 => 'The Terrifying Duo',
                14 => 'My Name is Monkey D. Luffy',
                15 => 'Gong',
                16 => 'Against Kuina',
                17 => 'Shimotsuki Village'
            ];

            Comic::create([
                'series_id' => $onePiece->id,
                'chapter_number' => $chapterNum,
                'title' => $chapterTitles[$i] ?? "Chapter {$i}",
                'description' => "One Piece Chapter {$i}",
                'cover_image' => "onepiece/Chapter {$chapterNum}/04.jpg", // Page 4 sebagai cover
                'pages' => $onePiecePages,
                'page_count' => count($onePiecePages)
            ]);
        }

        // 5. Sakamoto Days Series
        $sakamoto = Series::create([
            'title' => 'Sakamoto Days',
            'description' => 'Former legendary assassin living a peaceful life',
            'cover_image' => 'sakamotodays_cover.gif',
            'status' => 'ongoing'
        ]);

        // Sakamoto Days Chapters 01-07 (cover dari page 4)
        for ($i = 1; $i <= 7; $i++) {
            $chapterNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            
            $sakamotoPages = [];
            // Estimasi 30 pages per chapter
            for ($page = 1; $page <= 30; $page++) {
                $sakamotoPages[] = "sakomotodays/Chapter {$chapterNum}/" . str_pad($page, 2, '0', STR_PAD_LEFT) . ".jpg";
            }

            Comic::create([
                'series_id' => $sakamoto->id,
                'chapter_number' => $chapterNum,
                'title' => "Chapter {$i}",
                'description' => "Sakamoto Days Chapter {$i}",
                'cover_image' => "sakomotodays/Chapter {$chapterNum}/04.jpg", // Page 4 sebagai cover
                'pages' => $sakamotoPages,
                'page_count' => count($sakamotoPages)
            ]);
        }

        // 6. Solo Leveling Series
        $soloLeveling = Series::create([
            'title' => 'Solo Leveling',
            'description' => 'The journey of the weakest hunter becoming the strongest',
            'cover_image' => 'sololeveling_cover.gif',
            'status' => 'completed'
        ]);

        // Solo Leveling Chapters 00-10 (cover dari page 4)
        for ($i = 0; $i <= 10; $i++) {
            $chapterNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            
            $soloPages = [];
            // Hanya buat chapter yang actual ada file nya
            if ($i <= 2 || $i >= 5) { // Chapter yang punya cover image actual
                for ($page = 1; $page <= 40; $page++) {
                    $soloPages[] = "sololeveling/Chapter {$chapterNum}/" . str_pad($page, 2, '0', STR_PAD_LEFT) . ".jpg";
                }
                
                $chapterTitle = $i == 0 ? 'Prologue' : "Chapter {$i}";

                Comic::create([
                    'series_id' => $soloLeveling->id,
                    'chapter_number' => $chapterNum,
                    'title' => $chapterTitle,
                    'description' => "Solo Leveling {$chapterTitle}",
                    'cover_image' => "sololeveling/Chapter {$chapterNum}/04.jpg", // Page 4 sebagai cover
                    'pages' => $soloPages,
                    'page_count' => count($soloPages)
                ]);
            }
        }
    }
}