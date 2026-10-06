<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->json('details')->nullable();
        });

        foreach ($this->details() as $slug => $details) {
            DB::table('services')->where('slug', $slug)->update([
                'details' => json_encode($details),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('details');
        });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function details(): array
    {
        return [
            'high-conversion-google-ads' => [
                'price_note' => 'Inclusive of all taxes. Ad spend budget separate.',
                'audiences' => ['B2B Leads', 'B2C Sales', 'Local Footfall'],
                'deliverables' => [
                    'Dedicated Campaign Manager',
                    'Custom Landing Page Setup',
                    'Meta & Google Ads Integration',
                ],
                'full_deliverables' => [
                    'Search and display campaign structure',
                    'Conversion tracking and weekly optimization',
                    'Audience and keyword refinement',
                    'Monthly performance report',
                ],
                'reasons_heading' => 'Here are a few reasons why this campaign service is the right choice for your business:',
                'reasons' => [
                    ['title' => 'High-intent traffic', 'body' => 'Campaigns focus on people already searching for what you sell, instead of broad awareness alone.'],
                    ['title' => 'A manager on the account', 'body' => 'A dedicated campaign manager owns structure, bids, creative, and the weekly optimization cycle.'],
                    ['title' => 'Pages built to convert', 'body' => 'Each offer can land on a page set up for the click, rather than a generic homepage.'],
                    ['title' => 'Spend stays visible', 'body' => 'You see leads, cost per result, and what changed in the account every month. Media budget is billed separately.'],
                ],
            ],
            'instagram-marketing' => [
                'audiences' => ['Brand Awareness', 'Lead Generation', 'Community Growth'],
                'deliverables' => [
                    'Content calendar and publishing',
                    'Creative direction for the brand channels',
                    'Monthly performance report',
                ],
                'full_deliverables' => [
                    'Profile and highlight structure',
                    'Caption and creative batches',
                    'Community replies during business hours',
                    'Monthly insights review',
                ],
                'reasons_heading' => 'Here are a few reasons why Instagram marketing is a strong fit:',
                'reasons' => [
                    ['title' => 'A consistent presence', 'body' => 'The month is planned, written, and published so the brand does not go quiet between campaigns.'],
                    ['title' => 'Creative that matches the offer', 'body' => 'Posts are built around the products and proof points you actually sell.'],
                    ['title' => 'One team for the channels', 'body' => 'Content, publishing, and reporting sit with Sunrise instead of being split across freelancers.'],
                    ['title' => 'A clear monthly readout', 'body' => 'You see what was published and which posts drove profile visits, follows, and enquiries.'],
                ],
            ],
            'whatsapp-marketing' => [
                'audiences' => ['Customer Support', 'Promotions', 'Lead Follow-up'],
                'deliverables' => [
                    'One-month message calendar',
                    'Broadcast and reply templates',
                    'Handover ready to publish',
                ],
                'full_deliverables' => [
                    'Audience segments and send plan',
                    'Offer, reminder, and follow-up copy',
                    'Opt-in wording for the broadcasts',
                    'A publishing checklist for your team',
                ],
                'reasons_heading' => 'Here are a few reasons why a WhatsApp calendar helps:',
                'reasons' => [
                    ['title' => 'Messages people actually open', 'body' => 'The plan uses short, specific broadcasts instead of a generic newsletter pasted into chat.'],
                    ['title' => 'Ready for your team', 'body' => 'Copy, timing, and the send checklist are handed over so you can publish without rewriting it.'],
                    ['title' => 'Follow-up is included', 'body' => 'Promotions, reminders, and lead replies are planned as one sequence.'],
                    ['title' => 'A short engagement', 'body' => 'This is a one-time calendar, delivered in 7 days, not an open-ended retainer.'],
                ],
            ],
            'comprehensive-technical-seo' => [
                'audiences' => ['Local Business', 'National Brand', 'Ecommerce'],
                'deliverables' => [
                    'Technical site review',
                    'On-page and content findings',
                    'Prioritized fix list',
                ],
                'full_deliverables' => [
                    'Crawl, index, and speed findings',
                    'Title, heading, and internal-link review',
                    'A ranked fix list your team can execute',
                    'A readout of what to do first',
                ],
                'reasons_heading' => 'Here are a few reasons why this SEO audit is the right starting point:',
                'reasons' => [
                    ['title' => 'The blockers come first', 'body' => 'The audit separates issues that stop pages from ranking from the nice-to-have edits.'],
                    ['title' => 'A plan, not a pile of screenshots', 'body' => 'Findings are ordered so a developer or marketer can start without another workshop.'],
                    ['title' => 'Built for this site', 'body' => 'The review covers the templates, content, and tracking you already have.'],
                    ['title' => 'A fixed delivery window', 'body' => 'You get the audit in 14 days as a one-time engagement.'],
                ],
            ],
            'content-marketing-engine' => [
                'audiences' => ['Organic Search', 'Product Education', 'Thought Leadership'],
                'deliverables' => [
                    'Editorial plan',
                    'First set of articles and landing pages',
                    'On-page structure for each piece',
                ],
                'full_deliverables' => [
                    'Topic map tied to commercial pages',
                    'Article and landing-page drafts',
                    'Internal links back to the offers',
                    'A handover your team can publish',
                ],
                'reasons_heading' => 'Here are a few reasons why a content engine is worth starting:',
                'reasons' => [
                    ['title' => 'Topics tied to demand', 'body' => 'The plan starts from the searches and questions that lead to your offers.'],
                    ['title' => 'Pages, not only posts', 'body' => 'The first batch includes landing pages as well as articles.'],
                    ['title' => 'Ready to publish', 'body' => 'Structure, drafts, and internal links are handed over together.'],
                    ['title' => 'A defined first sprint', 'body' => 'This is a 21-day build of the system, not an unnamed content subscription.'],
                ],
            ],
            'email-marketing-retainer' => [
                'audiences' => ['Welcome Flows', 'Campaigns', 'Win-back'],
                'deliverables' => [
                    'Monthly campaign calendar',
                    'Automation flows',
                    'Performance reporting',
                ],
                'full_deliverables' => [
                    'Welcome, browse, and post-purchase flows',
                    'Campaign copy and send schedule',
                    'List hygiene recommendations',
                    'A monthly results note',
                ],
                'reasons_heading' => 'Here are a few reasons why an email retainer pays off:',
                'reasons' => [
                    ['title' => 'Owned audience', 'body' => 'Campaigns go to people who already know the brand, alongside the paid channels.'],
                    ['title' => 'Flows keep working', 'body' => 'Welcome and follow-up automation runs between the one-off sends.'],
                    ['title' => 'A monthly rhythm', 'body' => 'The calendar, copy, and report arrive as one retainer.'],
                    ['title' => 'Clear next tests', 'body' => 'Each month names what to repeat and what to change.'],
                ],
            ],
            'managed-monthly-seo-growth-accelerator' => [
                'deliverables' => [
                    'Monthly technical and content work',
                    'Ranking and traffic reporting',
                    'A prioritized backlog',
                ],
                'full_deliverables' => [
                    'On-page updates for priority URLs',
                    'Technical fixes agreed with your team',
                    'Content briefs for the next month',
                    'A monthly growth report',
                ],
                'reasons_heading' => 'Here are a few reasons why monthly SEO works better than a one-off:',
                'reasons' => [
                    ['title' => 'The work continues', 'body' => 'Rankings move after the audit. This retainer keeps the fixes and content going.'],
                    ['title' => 'A backlog you can see', 'body' => 'Each month starts from a short list, not a new strategy deck.'],
                    ['title' => 'Reporting in the same engagement', 'body' => 'Traffic, rankings, and what shipped are in one note.'],
                    ['title' => 'Room to extend', 'body' => 'Stay on a monthly cycle, or commit to 3, 6, or 12 months.'],
                ],
            ],
            'ppc-campaign-setup-management' => [
                'price_note' => 'Inclusive of all taxes. Ad spend budget separate.',
                'audiences' => ['Search Campaigns', 'Remarketing', 'Lead Generation'],
                'deliverables' => [
                    'Account and campaign setup',
                    'Tracking and conversion goals',
                    'First month of management',
                ],
                'full_deliverables' => [
                    'Campaign structure and negatives',
                    'Ad copy for the core offers',
                    'Conversion tracking checklist',
                    'A first-month optimization note',
                ],
                'reasons' => [
                    ['title' => 'Setup and management together', 'body' => 'The account is built and then run, instead of handed over half-finished.'],
                    ['title' => 'Tracking before spend', 'body' => 'Conversion goals are defined before the budget scales.'],
                    ['title' => 'Media budget stays separate', 'body' => 'The fee covers the work. Ad spend is not bundled into the price.'],
                    ['title' => 'A manager from day one', 'body' => 'The first month includes optimization, not only the launch checklist.'],
                ],
            ],
            'social-media-starter-package' => [
                'deliverables' => [
                    'Profile setup and branding',
                    'One month of content calendar',
                    'Publishing guidance for 3 platforms',
                ],
                'full_deliverables' => [
                    'Profile copy and visual direction',
                    'A 30-day content calendar',
                    'Caption drafts for the first posts',
                    'A handover for your team',
                ],
                'reasons' => [
                    ['title' => 'A complete start', 'body' => 'Setup, branding, and the first month of content are one package.'],
                    ['title' => 'Three platforms, one plan', 'body' => 'The calendar is written so the same offer can run without three separate briefs.'],
                    ['title' => 'Your team can publish it', 'body' => 'Guidance is included so the month does not depend on another login.'],
                    ['title' => 'A deal window', 'body' => 'This starter is priced as a current deal against the regular package rate.'],
                ],
            ],
            'accelerated-website-launch' => [
                'deliverables' => [
                    '5-page corporate website',
                    'Design and build in 14 days',
                    'Launch-ready handover',
                ],
                'full_deliverables' => [
                    'Homepage and four inner pages',
                    'Mobile layout and basic on-page SEO',
                    'Contact path and analytics snippet',
                    'A launch checklist',
                ],
                'reasons' => [
                    ['title' => 'A fixed page set', 'body' => 'Five pages, scoped so the site can launch without an open redesign.'],
                    ['title' => 'A 14-day build', 'body' => 'The schedule is short because the page count is agreed up front.'],
                    ['title' => 'Ready to deploy', 'body' => 'You get a site that can go live, not a deck of wireframes.'],
                    ['title' => 'Room to extend later', 'body' => 'More pages, a shop, or a content program can follow after launch.'],
                ],
            ],
            'comprehensive-seo-audit' => [
                'audiences' => ['Local Business', 'National Brand', 'Ecommerce'],
                'deliverables' => [
                    'Technical, on-page, and backlink review',
                    'Prioritized action plan',
                    'Readout of what to fix first',
                ],
                'full_deliverables' => [
                    'Index and speed findings',
                    'On-page issues by template',
                    'Backlink notes that matter',
                    'A sequenced action plan',
                ],
                'reasons' => [
                    ['title' => 'One pass across the site', 'body' => 'Technical, on-page, and backlink issues are reviewed together.'],
                    ['title' => 'Priority is explicit', 'body' => 'The plan says what to do first, not only what is imperfect.'],
                    ['title' => 'Useful to the people shipping', 'body' => 'Developers and marketers can pick up the list without a second translation.'],
                    ['title' => 'A deal on the audit', 'body' => 'The current price is the deal rate against the regular audit fee.'],
                ],
            ],
            'ecommerce-website-setup-development' => [
                'deliverables' => [
                    'Store setup and theme',
                    'Core shopping pages',
                    'Launch support',
                ],
                'full_deliverables' => [
                    'Catalog, cart, and checkout setup',
                    'Homepage and key collection pages',
                    'Payment and shipping configuration',
                    'A handover for the team running the store',
                ],
                'reasons' => [
                    ['title' => 'A store, not a brochure', 'body' => 'The build includes the shopping path, not only marketing pages.'],
                    ['title' => 'Setup is part of the work', 'body' => 'Theme, catalog structure, and checkout are configured together.'],
                    ['title' => 'Launch is included', 'body' => 'You are not left with a theme zip and a list of plugins.'],
                    ['title' => 'A starting price', 'body' => 'The listed price is the starting point for this scope.'],
                ],
            ],
            'complete-corporate-brand-identity-design' => [
                'audiences' => ['New Brand', 'Rebrand', 'Campaign Identity'],
                'deliverables' => [
                    'Logo and visual system',
                    'Color, type, and usage rules',
                    'Files your team can apply',
                ],
                'full_deliverables' => [
                    'Primary and secondary marks',
                    'Color and type specification',
                    'Basic stationery and social templates',
                    'A short usage guide',
                ],
                'reasons' => [
                    ['title' => 'A system, not a single logo', 'body' => 'The identity includes the rules for applying it after the files are delivered.'],
                    ['title' => 'Ready for the channels', 'body' => 'Social and basic stationery are part of the same system.'],
                    ['title' => 'Clear handover', 'body' => 'Your team gets files and a short guide, not only preview images.'],
                    ['title' => 'Built for the company', 'body' => 'The system is for the brand you are taking to market, not a generic template.'],
                ],
            ],
        ];
    }
};
