<?php

namespace App\Support\Profile;

use Illuminate\Support\Str;

final class CredentialCategory
{
    public const OPTIONS = [
        'ai_data' => 'AI & Data',
        'software' => 'Software & Development',
        'networking' => 'Networking & IT Support',
        'cybersecurity' => 'Cybersecurity',
        'cloud_enterprise' => 'Cloud & Enterprise',
        'project_management' => 'Project Management',
        'design_marketing' => 'Design & Marketing',
        'professional' => 'Professional Development',
    ];

    public static function classify(?string $category, array $values): string
    {
        if ($category && array_key_exists($category, self::OPTIONS)) {
            return $category;
        }

        $text = Str::lower(collect($values)->flatten()->filter()->implode(' '));

        return match (true) {
            Str::contains($text, ['cyber', 'security', 'ethical hacker', 'network defense', 'threat', 'endpoint']) => 'cybersecurity',
            Str::contains($text, ['project management', 'scrum', 'kanban', 'agile', 'six sigma', 'leadership', 'management']) => 'project_management',
            Str::contains($text, ['aws', 'amazon web services', 'cloud', 'azure', 'sap', 'enterprise', 'oracle']) => 'cloud_enterprise',
            Str::contains($text, ['artificial intelligence', 'generative ai', 'machine learning', 'data science', 'data analytics', 'business intelligence', 'power bi', 'analytics', 'anthropic']) => 'ai_data',
            Str::contains($text, ['network', 'iot', 'cisco', 'hardware', 'customer support', 'it support', 'english for it']) => 'networking',
            Str::contains($text, ['ux', 'design', 'marketing', 'e-commerce', 'digital content', 'canva', 'adobe']) => 'design_marketing',
            Str::contains($text, ['software', 'developer', 'programming', 'javascript', 'python', 'php', 'sql', 'laravel', 'api']) => 'software',
            default => 'professional',
        };
    }

    public static function label(string $category): string
    {
        return self::OPTIONS[$category] ?? self::OPTIONS['professional'];
    }

    public static function optionList(): array
    {
        return collect(self::OPTIONS)
            ->map(fn (string $label, string $value): array => compact('value', 'label'))
            ->values()
            ->all();
    }
}
