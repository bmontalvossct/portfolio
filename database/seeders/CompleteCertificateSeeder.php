<?php

namespace Database\Seeders;

use App\Models\Certificate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CompleteCertificateSeeder extends Seeder
{
    public function run(): void
    {
        // Public metadata snapshot only. Private certificate documents and Drive identifiers are intentionally excluded.
        $certificates = array (
  0 => 
  array (
    'seed_key' => 'certificate-21b6f3dc5aa1798cd7f0',
    'title' => 'Lean Six Sigma: Yellow Belt',
    'issuer' => 'Alison',
    'category' => 'project_management',
    'description' => 'Verified completion of Lean Six Sigma: Yellow Belt with an 89% final assessment score. Alison ID 59724336; 0-1 CPD hours completed.',
    'issued_on' => '2026-07-20',
    'verification_code' => NULL,
    'file_url' => 'https://alison.com/verify/31557408f8',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Alison',
      1 => 'Lean Six Sigma',
      2 => 'Yellow Belt',
      3 => 'DMAIC',
      4 => 'Process improvement',
      5 => 'Project management',
      6 => '89% assessment score',
    ),
    'sort_order' => 0,
    'is_featured' => true,
  ),
  1 => 
  array (
    'seed_key' => 'certificate-b72fcbe077a8bf4807a5',
    'title' => 'Sangfor Network Security and Endpoint Secure Technical Training',
    'issuer' => 'ITDEPOT / Sangfor',
    'category' => 'cybersecurity',
    'description' => 'Certificate of completion for Sangfor Network Security and Endpoint Secure Technical Training conducted on 24-25 March 2026.',
    'issued_on' => '2026-03-25',
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Sangfor',
      1 => 'Network security',
      2 => 'Endpoint security',
      3 => 'Cybersecurity',
      4 => 'ITDEPOT',
    ),
    'sort_order' => 1,
    'is_featured' => true,
  ),
  2 => 
  array (
    'seed_key' => 'certificate-1c4a0221ba5d2ba63593',
    'title' => 'Women Adding Value through Maximization of Online Platforms for Optimum Opportunities',
    'issuer' => 'Department of Information and Communications Technology',
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 2,
    'is_featured' => true,
  ),
  3 => 
  array (
    'seed_key' => 'certificate-2477a02920625a563a9f',
    'title' => '5G Cybersecurity Standards and Solutions and 6G New Technology Innovation',
    'issuer' => NULL,
    'category' => 'cybersecurity',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cybersecurity',
      1 => 'Certificate',
    ),
    'sort_order' => 3,
    'is_featured' => true,
  ),
  4 => 
  array (
    'seed_key' => 'certificate-510070062dade1e474da',
    'title' => 'Agile Project Methodologies',
    'issuer' => NULL,
    'category' => 'project_management',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Project Management',
      1 => 'Certificate',
    ),
    'sort_order' => 4,
    'is_featured' => true,
  ),
  5 => 
  array (
    'seed_key' => 'certificate-eb0b65a67f7cac4ca95f',
    'title' => 'AI at Work Analyze Customer Reviews',
    'issuer' => NULL,
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 5,
    'is_featured' => true,
  ),
  6 => 
  array (
    'seed_key' => 'certificate-351faaab50e20d737410',
    'title' => 'AWS Essentials',
    'issuer' => 'Amazon Web Services',
    'category' => 'cloud_enterprise',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Amazon Web Services',
      1 => 'Cloud & Enterprise',
      2 => 'Certificate',
    ),
    'sort_order' => 6,
    'is_featured' => true,
  ),
  7 => 
  array (
    'seed_key' => 'certificate-320f889f271fae036670',
    'title' => 'Foundations of Cyber Emergency Response Team',
    'issuer' => 'Department of Information and Communications Technology',
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 7,
    'is_featured' => true,
  ),
  8 => 
  array (
    'seed_key' => 'certificate-02625d9e8f617034f9a2',
    'title' => 'Cyber Emergency Response Team',
    'issuer' => 'Department of Information and Communications Technology',
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 8,
    'is_featured' => true,
  ),
  9 => 
  array (
    'seed_key' => 'certificate-104371778834439af21e',
    'title' => 'Cisco Introduction to Cybersecurity',
    'issuer' => 'Cisco',
    'category' => 'cybersecurity',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cisco',
      1 => 'Cybersecurity',
      2 => 'Certificate',
    ),
    'sort_order' => 9,
    'is_featured' => true,
  ),
  10 => 
  array (
    'seed_key' => 'certificate-2b424ec1c93ea2b0ccb2',
    'title' => 'Cisco Introduction to Data Science',
    'issuer' => 'Cisco',
    'category' => 'ai_data',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cisco',
      1 => 'AI & Data',
      2 => 'Certificate',
    ),
    'sort_order' => 10,
    'is_featured' => true,
  ),
  11 => 
  array (
    'seed_key' => 'certificate-35315c1e54f73a94791a',
    'title' => 'Cisco Introduction to IoT',
    'issuer' => 'Cisco',
    'category' => 'networking',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cisco',
      1 => 'Networking & IT Support',
      2 => 'Certificate',
    ),
    'sort_order' => 11,
    'is_featured' => true,
  ),
  12 => 
  array (
    'seed_key' => 'certificate-84a4be683a53eab28fb8',
    'title' => 'Cisco Network Basics',
    'issuer' => 'Cisco',
    'category' => 'networking',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cisco',
      1 => 'Networking & IT Support',
      2 => 'Certificate',
    ),
    'sort_order' => 12,
    'is_featured' => true,
  ),
  13 => 
  array (
    'seed_key' => 'certificate-2ab1ac7635c6ad2c5363',
    'title' => 'Cisco Routing Essentials',
    'issuer' => 'Cisco',
    'category' => 'networking',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cisco',
      1 => 'Networking & IT Support',
      2 => 'Certificate',
    ),
    'sort_order' => 13,
    'is_featured' => true,
  ),
  14 => 
  array (
    'seed_key' => 'certificate-5ff46f63d3506642ffd1',
    'title' => 'Collection of Geographic Coordinates of Health Facilities Using GPS Devices, GPS Enabled Android Mobile Device & Google Maps and Basic QGIS',
    'issuer' => 'Google / Coursera',
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'Professional Development',
      2 => 'Certificate',
    ),
    'sort_order' => 14,
    'is_featured' => true,
  ),
  15 => 
  array (
    'seed_key' => 'certificate-471d8aaa5850af7593ce',
    'title' => 'Configuration Management and the Cloud',
    'issuer' => NULL,
    'category' => 'project_management',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Project Management',
      1 => 'Certificate',
    ),
    'sort_order' => 15,
    'is_featured' => true,
  ),
  16 => 
  array (
    'seed_key' => 'certificate-7ac1360d85319036f20e',
    'title' => 'Creating Digital Content',
    'issuer' => 'Department of Information and Communications Technology',
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 16,
    'is_featured' => true,
  ),
  17 => 
  array (
    'seed_key' => 'certificate-058f8afcebeedc08e33a',
    'title' => 'Cyber Range Exercise 2025',
    'issuer' => 'Department of Information and Communications Technology',
    'category' => 'cybersecurity',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Department of Information and Communications Technology',
      1 => 'Cybersecurity',
      2 => 'Certificate',
    ),
    'sort_order' => 17,
    'is_featured' => true,
  ),
  18 => 
  array (
    'seed_key' => 'certificate-b32bf95e6f7b99118e6e',
    'title' => 'Cyber Security and DPA Framework',
    'issuer' => 'Department of Information and Communications Technology',
    'category' => 'cybersecurity',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cybersecurity',
      1 => 'Certificate',
    ),
    'sort_order' => 18,
    'is_featured' => true,
  ),
  19 => 
  array (
    'seed_key' => 'certificate-5abccf41ffd4f323d207',
    'title' => 'Cybersecurity Competency Framework',
    'issuer' => 'Department of Information and Communications Technology',
    'category' => 'cybersecurity',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Department of Information and Communications Technology',
      1 => 'Cybersecurity',
      2 => 'Certificate',
    ),
    'sort_order' => 19,
    'is_featured' => true,
  ),
  20 => 
  array (
    'seed_key' => 'certificate-ca1fd25a183f72c34caa',
    'title' => 'Cyber Threat Management',
    'issuer' => NULL,
    'category' => 'cybersecurity',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cybersecurity',
      1 => 'Certificate',
    ),
    'sort_order' => 20,
    'is_featured' => true,
  ),
  21 => 
  array (
    'seed_key' => 'certificate-e799076621bbad37638e',
    'title' => 'Data Analytics Ask Data Driven Decisions',
    'issuer' => NULL,
    'category' => 'ai_data',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'AI & Data',
      1 => 'Certificate',
    ),
    'sort_order' => 21,
    'is_featured' => true,
  ),
  22 => 
  array (
    'seed_key' => 'certificate-acf0c04318e084823d5d',
    'title' => 'Ecommerce Businesses Training',
    'issuer' => 'Department of Information and Communications Technology',
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 22,
    'is_featured' => true,
  ),
  23 => 
  array (
    'seed_key' => 'certificate-47909d20d48f40bd860a',
    'title' => 'Microsoft 365 Future Ready Skills',
    'issuer' => 'Microsoft',
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Microsoft',
      1 => 'Professional Development',
      2 => 'Certificate',
    ),
    'sort_order' => 23,
    'is_featured' => true,
  ),
  24 => 
  array (
    'seed_key' => 'certificate-a8e9508a48e52f89dcf4',
    'title' => 'Endpoint Security',
    'issuer' => NULL,
    'category' => 'cybersecurity',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cybersecurity',
      1 => 'Certificate',
    ),
    'sort_order' => 24,
    'is_featured' => true,
  ),
  25 => 
  array (
    'seed_key' => 'certificate-3893e2eac24f3a83afca',
    'title' => 'English for IT Advice and Time',
    'issuer' => NULL,
    'category' => 'networking',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Networking & IT Support',
      1 => 'Certificate',
    ),
    'sort_order' => 25,
    'is_featured' => true,
  ),
  26 => 
  array (
    'seed_key' => 'certificate-08a3bbb79fb5b0b5f3e1',
    'title' => 'English for IT Describing and Comparing',
    'issuer' => NULL,
    'category' => 'networking',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Networking & IT Support',
      1 => 'Certificate',
    ),
    'sort_order' => 26,
    'is_featured' => true,
  ),
  27 => 
  array (
    'seed_key' => 'certificate-ee605018ed109b241ab5',
    'title' => 'English for IT Needs and Responsibilities',
    'issuer' => NULL,
    'category' => 'networking',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Networking & IT Support',
      1 => 'Certificate',
    ),
    'sort_order' => 27,
    'is_featured' => true,
  ),
  28 => 
  array (
    'seed_key' => 'certificate-21ab05fcac9ebbd81f16',
    'title' => 'English for IT People and Quantities',
    'issuer' => NULL,
    'category' => 'networking',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Networking & IT Support',
      1 => 'Certificate',
    ),
    'sort_order' => 28,
    'is_featured' => true,
  ),
  29 => 
  array (
    'seed_key' => 'certificate-b786eb3aec1a99448ad9',
    'title' => 'English for IT 1',
    'issuer' => NULL,
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 29,
    'is_featured' => true,
  ),
  30 => 
  array (
    'seed_key' => 'certificate-bec985738b56232289f1',
    'title' => 'English for IT 2',
    'issuer' => NULL,
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 30,
    'is_featured' => true,
  ),
  31 => 
  array (
    'seed_key' => 'certificate-300f8bb60f9981d32305',
    'title' => 'Enterprise Security Governance Practice',
    'issuer' => NULL,
    'category' => 'cybersecurity',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Cybersecurity',
      1 => 'Certificate',
    ),
    'sort_order' => 31,
    'is_featured' => true,
  ),
  32 => 
  array (
    'seed_key' => 'certificate-1ce6e76f882691a27f08',
    'title' => 'Ethical Hacker',
    'issuer' => NULL,
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 32,
    'is_featured' => true,
  ),
  33 => 
  array (
    'seed_key' => 'certificate-2e14f635a1a88571b91a',
    'title' => 'Exploring End to End Business',
    'issuer' => NULL,
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Professional Development',
      1 => 'Certificate',
    ),
    'sort_order' => 33,
    'is_featured' => true,
  ),
  34 => 
  array (
    'seed_key' => 'certificate-6ef99ca31cc1865b16f0',
    'title' => 'Exploring SAP Business Technology Platform',
    'issuer' => 'SAP',
    'category' => 'cloud_enterprise',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'SAP',
      1 => 'Cloud & Enterprise',
      2 => 'Certificate',
    ),
    'sort_order' => 34,
    'is_featured' => true,
  ),
  35 => 
  array (
    'seed_key' => 'certificate-cbab92d4e08402b22f85',
    'title' => 'Google Digital Marketing & E-Commerce',
    'issuer' => 'Google / Coursera',
    'category' => 'design_marketing',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'Design & Marketing',
      2 => 'Certificate',
    ),
    'sort_order' => 35,
    'is_featured' => true,
  ),
  36 => 
  array (
    'seed_key' => 'certificate-6a1c7f60d03a95836397',
    'title' => 'Google Advanced Data Analytics',
    'issuer' => 'Google / Coursera',
    'category' => 'ai_data',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'AI & Data',
      2 => 'Certificate',
    ),
    'sort_order' => 36,
    'is_featured' => true,
  ),
  37 => 
  array (
    'seed_key' => 'certificate-62bd49e33c21b5a56d5e',
    'title' => 'Google Business Intelligence',
    'issuer' => 'Google / Coursera',
    'category' => 'ai_data',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'AI & Data',
      2 => 'Certificate',
    ),
    'sort_order' => 37,
    'is_featured' => true,
  ),
  38 => 
  array (
    'seed_key' => 'certificate-edd5230e11dfd7ea4ccf',
    'title' => 'Google Cybersecurity',
    'issuer' => 'Google / Coursera',
    'category' => 'cybersecurity',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'Cybersecurity',
      2 => 'Certificate',
    ),
    'sort_order' => 38,
    'is_featured' => true,
  ),
  39 => 
  array (
    'seed_key' => 'certificate-4527aa4d981d2a52e88a',
    'title' => 'Google Data Analytics Data Data Everywhere',
    'issuer' => 'Google / Coursera',
    'category' => 'ai_data',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'AI & Data',
      2 => 'Certificate',
    ),
    'sort_order' => 39,
    'is_featured' => true,
  ),
  40 => 
  array (
    'seed_key' => 'certificate-5a446d2135923ba06b80',
    'title' => 'Google Data Analytics',
    'issuer' => 'Google / Coursera',
    'category' => 'ai_data',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'AI & Data',
      2 => 'Certificate',
    ),
    'sort_order' => 40,
    'is_featured' => true,
  ),
  41 => 
  array (
    'seed_key' => 'certificate-edb3731b022bb6bb6aa7',
    'title' => 'Google IT Automation with Python',
    'issuer' => 'Google / Coursera',
    'category' => 'software',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'Software & Development',
      2 => 'Certificate',
    ),
    'sort_order' => 41,
    'is_featured' => true,
  ),
  42 => 
  array (
    'seed_key' => 'certificate-ffe8c80decffc63f4822',
    'title' => 'Google IT Support',
    'issuer' => 'Google / Coursera',
    'category' => 'networking',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'Networking & IT Support',
      2 => 'Certificate',
    ),
    'sort_order' => 42,
    'is_featured' => true,
  ),
  43 => 
  array (
    'seed_key' => 'certificate-90a2b2d85e33497b2c43',
    'title' => 'Google Prepare Data Exploration',
    'issuer' => 'Google / Coursera',
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'Professional Development',
      2 => 'Certificate',
    ),
    'sort_order' => 43,
    'is_featured' => true,
  ),
  44 => 
  array (
    'seed_key' => 'certificate-81d4b57e60d38a4541ed',
    'title' => 'Google Prepare for Data Exploration',
    'issuer' => 'Google / Coursera',
    'category' => 'professional',
    'description' => NULL,
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => '',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Google / Coursera',
      1 => 'Professional Development',
      2 => 'Certificate',
    ),
    'sort_order' => 44,
    'is_featured' => true,
  ),
  45 => 
  array (
    'seed_key' => 'certificate-4b7cff84a06a1fb4f679',
    'title' => 'Building with the Claude API',
    'issuer' => 'Anthropic',
    'category' => 'ai_data',
    'description' => 'Anthropic course covering practical application development with the Claude API.',
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => 'https://anthropic.skilljar.com/claude-with-the-anthropic-api',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Anthropic',
      1 => 'Claude API',
      2 => 'API development',
      3 => 'Artificial intelligence',
    ),
    'sort_order' => 45,
    'is_featured' => true,
  ),
  46 => 
  array (
    'seed_key' => 'certificate-ceb5ade6e09d64ccd3a4',
    'title' => 'Introduction to Agent Skills',
    'issuer' => 'Anthropic',
    'category' => 'ai_data',
    'description' => 'Anthropic course introducing reusable agent skills and their application in Claude workflows.',
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => 'https://anthropic.skilljar.com/introduction-to-agent-skills',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Anthropic',
      1 => 'Claude',
      2 => 'Agent skills',
      3 => 'Artificial intelligence',
    ),
    'sort_order' => 46,
    'is_featured' => true,
  ),
  47 => 
  array (
    'seed_key' => 'certificate-013532584511eee7aedb',
    'title' => 'Introduction to Model Context Protocol',
    'issuer' => 'Anthropic',
    'category' => 'ai_data',
    'description' => 'Anthropic course introducing Model Context Protocol concepts and integrations.',
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => 'https://anthropic.skilljar.com/introduction-to-model-context-protocol',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Anthropic',
      1 => 'Model Context Protocol',
      2 => 'MCP',
      3 => 'Artificial intelligence',
    ),
    'sort_order' => 47,
    'is_featured' => true,
  ),
  48 => 
  array (
    'seed_key' => 'certificate-b5ed854e2e9ffcc2d9bd',
    'title' => 'Omada Certified Network Administrator (OCNA) - Wireless',
    'issuer' => 'Omada by TP-Link',
    'category' => 'networking',
    'description' => 'Omada certification focused on wireless networking, deployment, configuration, and administration.',
    'issued_on' => NULL,
    'verification_code' => '57E94B6682EC4E95',
    'file_url' => 'https://training.tp-link.com/omada',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Omada',
      1 => 'TP-Link',
      2 => 'OCNA',
      3 => 'Wireless networking',
      4 => 'Network administration',
    ),
    'sort_order' => 48,
    'is_featured' => true,
  ),
  49 => 
  array (
    'seed_key' => 'certificate-006801d668f26e710502',
    'title' => 'Hikvision Training Camp',
    'issuer' => 'Hikvision',
    'category' => 'professional',
    'description' => 'Hikvision technical training covering video surveillance and integrated security technologies.',
    'issued_on' => NULL,
    'verification_code' => NULL,
    'file_url' => 'https://content.hikvision.com/htc-registration',
    'thumbnail_url' => NULL,
    'local_path' => NULL,
    'tags' => 
    array (
      0 => 'Hikvision',
      1 => 'Video surveillance',
      2 => 'Security systems',
      3 => 'Technical training',
    ),
    'sort_order' => 49,
    'is_featured' => true,
  ),
);

        foreach ($certificates as $data) {
            $certificate = Certificate::query()->where('title', $data['title'])->first();

            if (! $certificate) {
                $certificate = new Certificate([
                    'source' => 'portfolio_seed',
                    'external_id' => $data['seed_key'],
                ]);
            }

            $certificate->fill(Arr::except($data, ['seed_key']))->save();
        }
    }
}