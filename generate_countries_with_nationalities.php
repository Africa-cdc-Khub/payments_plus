<?php
/**
 * Generate Countries with Nationalities
 * This script converts the basic countries list to include nationalities
 */

// Load the new countries data
$jsonContent = file_get_contents('countries_new.json');

// Convert JavaScript object notation to valid JSON
$jsonContent = str_replace("'", '"', $jsonContent); // Replace single quotes with double quotes
$jsonContent = preg_replace('/(\w+):/', '"$1":', $jsonContent); // Add quotes around property names

$countriesData = json_decode($jsonContent, true);

if (!$countriesData) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
    echo "First 200 chars: " . substr($jsonContent, 0, 200) . "\n";
    die("Failed to load countries data\n");
}

echo "Loaded " . count($countriesData) . " countries\n";

// Function to generate nationality from country name
function generateNationality($countryName) {
    // Common nationality patterns
    $patterns = [
        // Countries ending with 'a' -> add 'n'
        '/^(.+)a$/' => '$1an',
        // Countries ending with 'ia' -> replace with 'ian'
        '/^(.+)ia$/' => '$1ian',
        // Countries ending with 'land' -> add 'er'
        '/^(.+)land$/' => '$1lander',
        // Countries ending with 'stan' -> add 'i'
        '/^(.+)stan$/' => '$1i',
        // Countries ending with 'y' -> replace with 'ian'
        '/^(.+)y$/' => '$1ian',
        // Countries ending with 'e' -> add 'an'
        '/^(.+)e$/' => '$1an',
        // Countries ending with 'o' -> add 'an'
        '/^(.+)o$/' => '$1an',
        // Countries ending with 'i' -> add 'an'
        '/^(.+)i$/' => '$1an',
        // Countries ending with 'u' -> add 'an'
        '/^(.+)u$/' => '$1an',
        // Default: add 'an'
        '/^(.+)$/' => '$1an'
    ];
    
    $nationality = $countryName;
    foreach ($patterns as $pattern => $replacement) {
        if (preg_match($pattern, $countryName, $matches)) {
            $nationality = preg_replace($pattern, $replacement, $countryName);
            break;
        }
    }
    
    // Special cases
    $specialCases = [
        'United States' => 'American',
        'United Kingdom' => 'British',
        'United Arab Emirates' => 'Emirati',
        'South Africa' => 'South African',
        'South Korea' => 'South Korean',
        'North Korea' => 'North Korean',
        'New Zealand' => 'New Zealander',
        'Czech Republic' => 'Czech',
        'Slovakia' => 'Slovak',
        'Slovenia' => 'Slovenian',
        'Croatia' => 'Croatian',
        'Serbia' => 'Serbian',
        'Bosnia and Herzegovina' => 'Bosnian',
        'Macedonia' => 'Macedonian',
        'Albania' => 'Albanian',
        'Romania' => 'Romanian',
        'Bulgaria' => 'Bulgarian',
        'Hungary' => 'Hungarian',
        'Poland' => 'Polish',
        'Germany' => 'German',
        'France' => 'French',
        'Spain' => 'Spanish',
        'Italy' => 'Italian',
        'Portugal' => 'Portuguese',
        'Greece' => 'Greek',
        'Turkey' => 'Turkish',
        'Russia' => 'Russian',
        'Ukraine' => 'Ukrainian',
        'Belarus' => 'Belarusian',
        'Lithuania' => 'Lithuanian',
        'Latvia' => 'Latvian',
        'Estonia' => 'Estonian',
        'Finland' => 'Finnish',
        'Sweden' => 'Swedish',
        'Norway' => 'Norwegian',
        'Denmark' => 'Danish',
        'Iceland' => 'Icelandic',
        'Ireland' => 'Irish',
        'Netherlands' => 'Dutch',
        'Belgium' => 'Belgian',
        'Switzerland' => 'Swiss',
        'Austria' => 'Austrian',
        'Luxembourg' => 'Luxembourgish',
        'Monaco' => 'Monégasque',
        'Liechtenstein' => 'Liechtensteiner',
        'Malta' => 'Maltese',
        'Cyprus' => 'Cypriot',
        'Israel' => 'Israeli',
        'Palestine' => 'Palestinian',
        'Jordan' => 'Jordanian',
        'Lebanon' => 'Lebanese',
        'Syria' => 'Syrian',
        'Iraq' => 'Iraqi',
        'Iran' => 'Iranian',
        'Saudi Arabia' => 'Saudi',
        'Kuwait' => 'Kuwaiti',
        'Qatar' => 'Qatari',
        'Bahrain' => 'Bahraini',
        'Oman' => 'Omani',
        'Yemen' => 'Yemeni',
        'Afghanistan' => 'Afghan',
        'Pakistan' => 'Pakistani',
        'India' => 'Indian',
        'Bangladesh' => 'Bangladeshi',
        'Sri Lanka' => 'Sri Lankan',
        'Maldives' => 'Maldivian',
        'Nepal' => 'Nepalese',
        'Bhutan' => 'Bhutanese',
        'Myanmar' => 'Burmese',
        'Thailand' => 'Thai',
        'Laos' => 'Laotian',
        'Vietnam' => 'Vietnamese',
        'Cambodia' => 'Cambodian',
        'Malaysia' => 'Malaysian',
        'Singapore' => 'Singaporean',
        'Indonesia' => 'Indonesian',
        'Philippines' => 'Filipino',
        'Brunei' => 'Bruneian',
        'China' => 'Chinese',
        'Japan' => 'Japanese',
        'South Korea' => 'South Korean',
        'North Korea' => 'North Korean',
        'Mongolia' => 'Mongolian',
        'Kazakhstan' => 'Kazakhstani',
        'Uzbekistan' => 'Uzbek',
        'Kyrgyzstan' => 'Kyrgyzstani',
        'Tajikistan' => 'Tajikistani',
        'Turkmenistan' => 'Turkmen',
        'Azerbaijan' => 'Azerbaijani',
        'Armenia' => 'Armenian',
        'Georgia' => 'Georgian',
        'Canada' => 'Canadian',
        'Mexico' => 'Mexican',
        'Guatemala' => 'Guatemalan',
        'Belize' => 'Belizean',
        'El Salvador' => 'Salvadoran',
        'Honduras' => 'Honduran',
        'Nicaragua' => 'Nicaraguan',
        'Costa Rica' => 'Costa Rican',
        'Panama' => 'Panamanian',
        'Cuba' => 'Cuban',
        'Jamaica' => 'Jamaican',
        'Haiti' => 'Haitian',
        'Dominican Republic' => 'Dominican',
        'Puerto Rico' => 'Puerto Rican',
        'Trinidad and Tobago' => 'Trinidadian',
        'Barbados' => 'Barbadian',
        'Saint Lucia' => 'Saint Lucian',
        'Saint Vincent and the Grenadines' => 'Saint Vincentian',
        'Grenada' => 'Grenadian',
        'Antigua and Barbuda' => 'Antiguan',
        'Saint Kitts and Nevis' => 'Kittitian',
        'Dominica' => 'Dominican',
        'Brazil' => 'Brazilian',
        'Argentina' => 'Argentine',
        'Chile' => 'Chilean',
        'Uruguay' => 'Uruguayan',
        'Paraguay' => 'Paraguayan',
        'Bolivia' => 'Bolivian',
        'Peru' => 'Peruvian',
        'Ecuador' => 'Ecuadorian',
        'Colombia' => 'Colombian',
        'Venezuela' => 'Venezuelan',
        'Guyana' => 'Guyanese',
        'Suriname' => 'Surinamese',
        'Australia' => 'Australian',
        'New Zealand' => 'New Zealander',
        'Papua New Guinea' => 'Papua New Guinean',
        'Fiji' => 'Fijian',
        'Samoa' => 'Samoan',
        'Tonga' => 'Tongan',
        'Vanuatu' => 'Vanuatuan',
        'Solomon Islands' => 'Solomon Islander',
        'Kiribati' => 'I-Kiribati',
        'Tuvalu' => 'Tuvaluan',
        'Nauru' => 'Nauruan',
        'Palau' => 'Palauan',
        'Marshall Islands' => 'Marshallese',
        'Micronesia' => 'Micronesian',
        'Algeria' => 'Algerian',
        'Angola' => 'Angolan',
        'Benin' => 'Beninese',
        'Botswana' => 'Motswana',
        'Burkina Faso' => 'Burkinabé',
        'Burundi' => 'Burundian',
        'Cameroon' => 'Cameroonian',
        'Cape Verde' => 'Cape Verdean',
        'Central African Republic' => 'Central African',
        'Chad' => 'Chadian',
        'Comoros' => 'Comoran',
        'Congo' => 'Congolese',
        'Democratic Republic of the Congo' => 'Congolese',
        'Côte d\'Ivoire' => 'Ivorian',
        'Djibouti' => 'Djiboutian',
        'Egypt' => 'Egyptian',
        'Equatorial Guinea' => 'Equatorial Guinean',
        'Eritrea' => 'Eritrean',
        'Ethiopia' => 'Ethiopian',
        'Gabon' => 'Gabonese',
        'Gambia' => 'Gambian',
        'Ghana' => 'Ghanaian',
        'Guinea' => 'Guinean',
        'Guinea-Bissau' => 'Guinea-Bissauan',
        'Kenya' => 'Kenyan',
        'Lesotho' => 'Mosotho',
        'Liberia' => 'Liberian',
        'Libya' => 'Libyan',
        'Madagascar' => 'Malagasy',
        'Malawi' => 'Malawian',
        'Mali' => 'Malian',
        'Mauritania' => 'Mauritanian',
        'Mauritius' => 'Mauritian',
        'Morocco' => 'Moroccan',
        'Mozambique' => 'Mozambican',
        'Namibia' => 'Namibian',
        'Niger' => 'Nigerien',
        'Nigeria' => 'Nigerian',
        'Rwanda' => 'Rwandan',
        'São Tomé and Príncipe' => 'São Toméan',
        'Senegal' => 'Senegalese',
        'Seychelles' => 'Seychellois',
        'Sierra Leone' => 'Sierra Leonean',
        'Somalia' => 'Somali',
        'South Sudan' => 'South Sudanese',
        'Sudan' => 'Sudanese',
        'Swaziland' => 'Swazi',
        'Tanzania' => 'Tanzanian',
        'Togo' => 'Togolese',
        'Tunisia' => 'Tunisian',
        'Uganda' => 'Ugandan',
        'Zambia' => 'Zambian',
        'Zimbabwe' => 'Zimbabwean'
    ];
    
    if (isset($specialCases[$countryName])) {
        return $specialCases[$countryName];
    }
    
    return $nationality;
}

// Convert countries to include nationalities
$countriesWithNationalities = [];
foreach ($countriesData as $country) {
    $nationality = generateNationality($country['name']);
    $countriesWithNationalities[] = [
        'code' => $country['code'],
        'name' => $country['name'],
        'nationality' => $nationality
    ];
}

// Sort by country name
usort($countriesWithNationalities, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// Save to new file
$jsonData = json_encode($countriesWithNationalities, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents('data/countries.json', $jsonData);

echo "Generated countries with nationalities:\n";
echo "Total countries: " . count($countriesWithNationalities) . "\n";

// Show some examples
echo "\nSample entries:\n";
for ($i = 0; $i < 10; $i++) {
    $country = $countriesWithNationalities[$i];
    echo "- {$country['name']} ({$country['nationality']})\n";
}

echo "\nCountries file updated successfully!\n";
?>
