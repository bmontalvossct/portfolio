<?php

namespace App\Support\Profile;

/** Public portfolio content published with the application; private and live data stays outside this snapshot. */
class VersionedPortfolioContent
{
    private ?array $content = null;

    public function get(string $section): array
    {
        $this->content ??= json_decode(file_get_contents(resource_path('data/portfolio-content.json')), true, 512, JSON_THROW_ON_ERROR);

        $items = $this->bundledMedia($this->content[$section] ?? []);

        if ($section === 'certificates') {
            $items = array_map(function (array $certificate): array {
                $public = array_intersect_key($certificate, array_flip([
                    'id', 'title', 'issuer', 'category', 'category_label', 'description',
                    'issued_on', 'issued_on_sort', 'file_url', 'thumbnail_url',
                    'provider_name', 'provider_initials', 'tags', 'is_featured',
                    'sort_order', 'created_at_sort',
                ]));
                $url = $public['file_url'] ?? null;
                $public['file_url'] = PublicCertificateExposure::verificationUrl(is_string($url) ? $url : null);
                $public['verification_code'] = null;

                return $public;
            }, $items);
        }

        if ($section === 'badges') {
            foreach ($items as &$badge) {
                $badge['category'] = CredentialCategory::classify($badge['category'], [
                    $badge['name'], $badge['issuer'], $badge['description'], $badge['skills'],
                ]);
                $badge['category_label'] = CredentialCategory::label($badge['category']);
            }
        }

        return $items;
    }

    private function bundledMedia(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->bundledMedia($value);
            } elseif (is_string($value) && str_starts_with($value, '/storage/portfolio/')) {
                $path = '/repository-media/portfolio/'.substr($value, strlen('/storage/portfolio/'));
                if (is_file(public_path(ltrim($path, '/')))) {
                    $data[$key] = $path;
                }
            }
        }

        return $data;
    }
}
