<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Building blocks used to assemble realistic-sounding headlines and bodies
     * without relying on an external Faker locale.
     */
    private array $topics = [
        'Artificial Intelligence', 'Quantum Computing', 'Renewable Energy', 'Space Exploration',
        'Global Markets', 'Climate Policy', 'Cybersecurity', 'Electric Vehicles', 'Biotechnology',
        'Semiconductors', 'Digital Privacy', 'Remote Work', 'Cryptocurrency', 'Public Health',
        'Ocean Science', 'Urban Mobility', 'Open Source', 'Robotics', 'Neuroscience', 'Astronomy',
    ];

    private array $headlineTemplates = [
        'Breakthrough in :topic Reshapes the Industry',
        ':topic Faces a Defining Year as Investment Surges',
        'How :topic Is Quietly Changing Everyday Life',
        'Researchers Unveil New Approach to :topic',
        'The Hidden Costs Behind the :topic Boom',
        'Inside the Race to Lead :topic',
        'Why Experts Are Rethinking :topic',
        ':topic Reaches a Turning Point, Analysts Say',
        'A New Era for :topic Begins to Take Shape',
        'Global Leaders Gather to Debate the Future of :topic',
        'Startups Bet Big on :topic Despite Headwinds',
        'What the Latest :topic Report Really Means',
    ];

    private array $authors = [
        'Elena Vasquez', 'Marcus Chen', 'Priya Nair', 'James Okafor', 'Sofia Lindqvist',
        'Daniel Rosenberg', 'Aisha Rahman', 'Tomas Novak', 'Mei Lin', 'Oliver Bennett',
        'Nadia Hassan', 'Lucas Moreau',
    ];

    private array $sentences = [
        'Analysts say the shift could redefine how organisations allocate resources over the next decade.',
        'Early adopters report measurable gains, though questions about long-term sustainability remain.',
        'Critics caution that the rapid pace of change may outstrip existing regulatory frameworks.',
        'The development arrives amid growing public scrutiny and a wave of new investment.',
        'Industry insiders describe the moment as both an opportunity and a significant challenge.',
        'Researchers emphasise that more independent verification will be needed before firm conclusions.',
        'Supporters argue the benefits will eventually reach a far broader segment of the population.',
        'The announcement sent ripples through markets, with several competitors responding within days.',
        'Observers note that smaller players may struggle to keep pace with the leading firms.',
        'Policymakers are now weighing how best to balance innovation against potential risks.',
        'Field trials suggest the approach is robust, but scaling it remains an open problem.',
        'For ordinary consumers, the practical impact may take years to become fully visible.',
    ];

    public function run(): void
    {
        $count = 55;
        $usedSlugs = [];

        for ($i = 0; $i < $count; $i++) {
            $topic = $this->topics[$i % count($this->topics)];
            $template = $this->headlineTemplates[($i * 7 + 3) % count($this->headlineTemplates)];
            $title = str_replace(':topic', $topic, $template);

            // Guarantee unique slugs even when titles repeat.
            $baseSlug = Str::slug($title);
            $slug = $baseSlug;
            $suffix = 2;
            while (in_array($slug, $usedSlugs, true)) {
                $slug = $baseSlug.'-'.$suffix++;
            }
            $usedSlugs[] = $slug;

            $content = $this->buildContent($topic, $i);
            $excerpt = Str::limit(strip_tags(str_replace(["\n\n", "\n"], ' ', $content)), 150);

            // Spread publish dates across the past ~90 days, newest first.
            $publishedAt = Carbon::now()
                ->subDays($i * 90 / $count)
                ->subHours(($i * 13) % 24)
                ->subMinutes(($i * 37) % 60);

            News::create([
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content' => $content,
                'image_url' => null,
                'author' => $this->authors[$i % count($this->authors)],
                'published_at' => $publishedAt,
            ]);
        }
    }

    private function buildContent(string $topic, int $seed): string
    {
        $paragraphs = [];
        $paragraphCount = 4 + ($seed % 3); // 4–6 paragraphs

        $intro = sprintf(
            'A wave of renewed attention is reshaping the world of %s. Over recent months, a steady stream of developments has pushed the field from niche interest into the centre of mainstream conversation.',
            strtolower($topic)
        );
        $paragraphs[] = $intro;

        for ($p = 0; $p < $paragraphCount; $p++) {
            $lines = [];
            $lineCount = 3 + (($seed + $p) % 3); // 3–5 sentences
            for ($l = 0; $l < $lineCount; $l++) {
                $lines[] = $this->sentences[($seed + $p * 3 + $l) % count($this->sentences)];
            }
            $paragraphs[] = implode(' ', $lines);
        }

        $paragraphs[] = sprintf(
            'Whether %s lives up to the current excitement will depend on the choices made in the coming year. For now, the only certainty is that the conversation is far from over.',
            strtolower($topic)
        );

        return implode("\n\n", $paragraphs);
    }
}
