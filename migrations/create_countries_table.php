<?php
/**
 * Migration: Create countries table and populate with data
 * This migration creates the countries table and inserts all country data
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../functions.php';

function createCountriesTable() {
    $pdo = getConnection();
    
    try {
        // Create countries table
        $sql = "
        CREATE TABLE IF NOT EXISTS countries (
            id SERIAL PRIMARY KEY,
            code VARCHAR(2) NOT NULL,
            name VARCHAR(200) NOT NULL,
            nationality VARCHAR(200),
            continent VARCHAR(50),
            iso2_code VARCHAR(2),
            iso3_code VARCHAR(3),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ";
        
        $pdo->exec($sql);
        echo "✅ Countries table created successfully\n";
        
        // Check if data already exists
        $countStmt = $pdo->query("SELECT COUNT(*) FROM countries");
        $count = $countStmt->fetchColumn();
        
        if ($count > 0) {
            echo "ℹ️  Countries table already has data ($count records). Skipping data insertion.\n";
            return true;
        }
        
        // Insert countries data
        $countriesData = [
            ['AF','Afghanistan','Afghan','Asia','AF','AFG'],
            ['AL','Albania','Albanian','Europe','AL','ALB'],
            ['DZ','Algeria','Algerian','Africa','DZ','DZA'],
            ['AD','Andorra','Andorran','Europe','AD','AND'],
            ['AO','Angola','Angolan','Africa','AO','AGO'],
            ['AG','Antigua and Barbuda','Antiguan or Barbudan','North America','AG','ATG'],
            ['AR','Argentina','Argentinian','South America','AR','ARG'],
            ['AM','Armenia','Armenian','Asia','AM','ARM'],
            ['AU','Australia','Australian','Oceania','AU','AUS'],
            ['AT','Austria','Austrian','Europe','AT','AUT'],
            ['AZ','Azerbaijan','Azerbaijani','Asia','AZ','AZE'],
            ['BS','Bahamas','Bahamian','North America','BS','BHS'],
            ['BH','Bahrain','Bahraini','Asia','BH','BHR'],
            ['BD','Bangladesh','Bangladeshi','Asia','BD','BGD'],
            ['BB','Barbados','Barbadian','North America','BB','BRB'],
            ['BY','Belarus','Belarusian','Europe','BY','BLR'],
            ['BE','Belgium','Belgian','Europe','BE','BEL'],
            ['BZ','Belize','Belizean','North America','BZ','BLZ'],
            ['BJ','Benin','Beninese','Africa','BJ','BEN'],
            ['BT','Bhutan','Bhutanese','Asia','BT','BTN'],
            ['BO','Bolivia (Plurinational State of)','Bolivian','South America','BO','BOL'],
            ['BA','Bosnia and Herzegovina','Bosnian or Herzegovinian','Europe','BA','BIH'],
            ['BW','Botswana','Motswana','Africa','BW','BWA'],
            ['BR','Brazil','Brazilian','South America','BR','BRA'],
            ['BN','Brunei Darussalam','Bruneian','Asia','BN','BRN'],
            ['BG','Bulgaria','Bulgarian','Europe','BG','BGR'],
            ['BF','Burkina Faso','Burkinabé','Africa','BF','BFA'],
            ['BI','Burundi','Burundian','Africa','BI','BDI'],
            ['CV','Cabo Verde','Cape Verdean','Africa','CV','CPV'],
            ['KH','Cambodia','Cambodian','Asia','KH','KHM'],
            ['CM','Cameroon','Cameroonian','Africa','CM','CMR'],
            ['CA','Canada','Canadian','North America','CA','CAN'],
            ['CF','Central African Republic','Central African','Africa','CF','CAF'],
            ['TD','Chad','Chadian','Africa','TD','TCD'],
            ['CL','Chile','Chilean','South America','CL','CHL'],
            ['CN','China','Chinese','Asia','CN','CHN'],
            ['CO','Colombia','Colombian','South America','CO','COL'],
            ['KM','Comoros','Comorian','Africa','KM','COM'],
            ['CG','Congo','Congolese','Africa','CG','COG'],
            ['CD','Congo, Democratic Republic of the Congo','Congolese','Africa','CD','COD'],
            ['CR','Costa Rica','Costa Rican','North America','CR','CRI'],
            ['CI','Côte d\'Ivoire','Ivorian','Africa','CI','CIV'],
            ['HR','Croatia','Croatian','Europe','HR','HRV'],
            ['CU','Cuba','Cuban','North America','CU','CUB'],
            ['CY','Cyprus','Cypriot','Asia','CY','CYP'],
            ['CZ','Czechia','Czech','Europe','CZ','CZE'],
            ['DK','Denmark','Danish','Europe','DK','DNK'],
            ['DJ','Djibouti','Djiboutian','Africa','DJ','DJI'],
            ['DM','Dominica','Dominican','North America','DM','DMA'],
            ['DO','Dominican Republic','Dominican','North America','DO','DOM'],
            ['EC','Ecuador','Ecuadorian','South America','EC','ECU'],
            ['EG','Egypt','Egyptian','Africa','EG','EGY'],
            ['SV','El Salvador','Salvadoran','North America','SV','SLV'],
            ['GQ','Equatorial Guinea','Equatorial Guinean','Africa','GQ','GNQ'],
            ['ER','Eritrea','Eritrean','Africa','ER','ERI'],
            ['EE','Estonia','Estonian','Europe','EE','EST'],
            ['SZ','Eswatini','Swazi','Africa','SZ','SWZ'],
            ['ET','Ethiopia','Ethiopian','Africa','ET','ETH'],
            ['FJ','Fiji','Fijian','Oceania','FJ','FJI'],
            ['FI','Finland','Finnish','Europe','FI','FIN'],
            ['FR','France','French','Europe','FR','FRA'],
            ['GA','Gabon','Gabonese','Africa','GA','GAB'],
            ['GM','Gambia','Gambian','Africa','GM','GMB'],
            ['GE','Georgia','Georgian','Asia','GE','GEO'],
            ['DE','Germany','German','Europe','DE','DEU'],
            ['GH','Ghana','Ghanaian','Africa','GH','GHA'],
            ['GR','Greece','Greek','Europe','GR','GRC'],
            ['GD','Grenada','Grenadian','North America','GD','GRD'],
            ['GT','Guatemala','Guatemalan','North America','GT','GTM'],
            ['GN','Guinea','Guinean','Africa','GN','GIN'],
            ['GW','Guinea-Bissau','Bissau-Guinean','Africa','GW','GNB'],
            ['GY','Guyana','Guyanese','South America','GY','GUY'],
            ['HT','Haiti','Haitian','North America','HT','HTI'],
            ['HN','Honduras','Honduran','North America','HN','HND'],
            ['HU','Hungary','Hungarian','Europe','HU','HUN'],
            ['IS','Iceland','Icelander','Europe','IS','ISL'],
            ['IN','India','Indian','Asia','IN','IND'],
            ['ID','Indonesia','Indonesian','Asia','ID','IDN'],
            ['IR','Iran (Islamic Republic of)','Iranian','Asia','IR','IRN'],
            ['IQ','Iraq','Iraqi','Asia','IQ','IRQ'],
            ['IE','Ireland','Irish','Europe','IE','IRL'],
            ['IL','Israel','Israeli','Asia','IL','ISR'],
            ['IT','Italy','Italian','Europe','IT','ITA'],
            ['JM','Jamaica','Jamaican','North America','JM','JAM'],
            ['JP','Japan','Japanese','Asia','JP','JPN'],
            ['JO','Jordan','Jordanian','Asia','JO','JOR'],
            ['KZ','Kazakhstan','Kazakhstani','Asia','KZ','KAZ'],
            ['KE','Kenya','Kenyan','Africa','KE','KEN'],
            ['KI','Kiribati','I-Kiribati','Oceania','KI','KIR'],
            ['KP','Korea (Democratic People\'s Republic of)','North Korean','Asia','KP','PRK'],
            ['KR','Korea (Republic of)','South Korean','Asia','KR','KOR'],
            ['KW','Kuwait','Kuwaiti','Asia','KW','KWT'],
            ['KG','Kyrgyzstan','Kyrgyzstani','Asia','KG','KGZ'],
            ['LA','Lao People\'s Democratic Republic','Lao','Asia','LA','LAO'],
            ['LV','Latvia','Latvian','Europe','LV','LVA'],
            ['LB','Lebanon','Lebanese','Asia','LB','LBN'],
            ['LS','Lesotho','Mosotho','Africa','LS','LSO'],
            ['LR','Liberia','Liberian','Africa','LR','LBR'],
            ['LY','Libya','Libyan','Africa','LY','LBY'],
            ['LI','Liechtenstein','Liechtensteiner','Europe','LI','LIE'],
            ['LT','Lithuania','Lithuanian','Europe','LT','LTU'],
            ['LU','Luxembourg','Luxembourger','Europe','LU','LUX'],
            ['MG','Madagascar','Malagasy','Africa','MG','MDG'],
            ['MW','Malawi','Malawian','Africa','MW','MWI'],
            ['MY','Malaysia','Malaysian','Asia','MY','MYS'],
            ['MV','Maldives','Maldivian','Asia','MV','MDV'],
            ['ML','Mali','Malian','Africa','ML','MLI'],
            ['MT','Malta','Maltese','Europe','MT','MLT'],
            ['MH','Marshall Islands','Marshallese','Oceania','MH','MHL'],
            ['MR','Mauritania','Mauritanian','Africa','MR','MRT'],
            ['MU','Mauritius','Mauritian','Africa','MU','MUS'],
            ['MX','Mexico','Mexican','North America','MX','MEX'],
            ['FM','Micronesia (Federated States of)','Micronesian','Oceania','FM','FSM'],
            ['MD','Moldova (Republic of)','Moldovan','Europe','MD','MDA'],
            ['MC','Monaco','Monégasque','Europe','MC','MCO'],
            ['MN','Mongolia','Mongolian','Asia','MN','MNG'],
            ['ME','Montenegro','Montenegrin','Europe','ME','MNE'],
            ['MA','Morocco','Moroccan','Africa','MA','MAR'],
            ['MZ','Mozambique','Mozambican','Africa','MZ','MOZ'],
            ['MM','Myanmar','Burmese','Asia','MM','MMR'],
            ['NA','Namibia','Namibian','Africa','NA','NAM'],
            ['NR','Nauru','Nauruan','Oceania','NR','NRU'],
            ['NP','Nepal','Nepalese','Asia','NP','NPL'],
            ['NL','Netherlands','Dutch','Europe','NL','NLD'],
            ['NZ','New Zealand','New Zealander','Oceania','NZ','NZL'],
            ['NI','Nicaragua','Nicaraguan','North America','NI','NIC'],
            ['NE','Niger','Nigerien','Africa','NE','NER'],
            ['NG','Nigeria','Nigerian','Africa','NG','NGA'],
            ['MK','North Macedonia','Macedonian','Europe','MK','MKD'],
            ['NO','Norway','Norwegian','Europe','NO','NOR'],
            ['OM','Oman','Omani','Asia','OM','OMN'],
            ['PK','Pakistan','Pakistani','Asia','PK','PAK'],
            ['PW','Palau','Palauan','Oceania','PW','PLW'],
            ['PS','Palestine, State of','Palestinian','Asia','PS','PSE'],
            ['PA','Panama','Panamanian','North America','PA','PAN'],
            ['PG','Papua New Guinea','Papua New Guinean','Oceania','PG','PNG'],
            ['PY','Paraguay','Paraguayan','South America','PY','PRY'],
            ['PE','Peru','Peruvian','South America','PE','PER'],
            ['PH','Philippines','Filipino','Asia','PH','PHL'],
            ['PL','Poland','Polish','Europe','PL','POL'],
            ['PT','Portugal','Portuguese','Europe','PT','PRT'],
            ['QA','Qatar','Qatari','Asia','QA','QAT'],
            ['RO','Romania','Romanian','Europe','RO','ROU'],
            ['RU','Russian Federation','Russian','Europe/Asia','RU','RUS'],
            ['RW','Rwanda','Rwandan','Africa','RW','RWA'],
            ['KN','Saint Kitts and Nevis','Kittitian or Nevisian','North America','KN','KNA'],
            ['LC','Saint Lucia','Saint Lucian','North America','LC','LCA'],
            ['VC','Saint Vincent and the Grenadines','Saint Vincentian','North America','VC','VCT'],
            ['WS','Samoa','Samoan','Oceania','WS','WSM'],
            ['SM','San Marino','Sammarinese','Europe','SM','SMR'],
            ['ST','Sao Tome and Principe','São Toméan','Africa','ST','STP'],
            ['SA','Saudi Arabia','Saudi','Asia','SA','SAU'],
            ['SN','Senegal','Senegalese','Africa','SN','SEN'],
            ['RS','Serbia','Serbian','Europe','RS','SRB'],
            ['SC','Seychelles','Seychellois','Africa','SC','SYC'],
            ['SL','Sierra Leone','Sierra Leonean','Africa','SL','SLE'],
            ['SG','Singapore','Singaporean','Asia','SG','SGP'],
            ['SK','Slovakia','Slovak','Europe','SK','SVK'],
            ['SI','Slovenia','Slovene','Europe','SI','SVN'],
            ['SB','Solomon Islands','Solomon Islander','Oceania','SB','SLB'],
            ['SO','Somalia','Somali','Africa','SO','SOM'],
            ['ZA','South Africa','South African','Africa','ZA','ZAF'],
            ['SS','South Sudan','South Sudanese','Africa','SS','SSD'],
            ['ES','Spain','Spanish','Europe','ES','ESP'],
            ['LK','Sri Lanka','Sri Lankan','Asia','LK','LKA'],
            ['SD','Sudan','Sudanese','Africa','SD','SDN'],
            ['SR','Suriname','Surinamese','South America','SR','SUR'],
            ['SE','Sweden','Swedish','Europe','SE','SWE'],
            ['CH','Switzerland','Swiss','Europe','CH','CHE'],
            ['SY','Syrian Arab Republic','Syrian','Asia','SY','SYR'],
            ['TJ','Tajikistan','Tajikistani','Asia','TJ','TJK'],
            ['TZ','Tanzania, United Republic of','Tanzanian','Africa','TZ','TZA'],
            ['TH','Thailand','Thai','Asia','TH','THA'],
            ['TL','Timor-Leste','Timorese','Asia','TL','TLS'],
            ['TG','Togo','Togolese','Africa','TG','TGO'],
            ['TO','Tonga','Tongan','Oceania','TO','TON'],
            ['TT','Trinidad and Tobago','Trinidadian or Tobagonian','North America','TT','TTO'],
            ['TN','Tunisia','Tunisian','Africa','TN','TUN'],
            ['TR','Turkey','Turkish','Asia/Europe','TR','TUR'],
            ['TM','Turkmenistan','Turkmen','Asia','TM','TKM'],
            ['TV','Tuvalu','Tuvaluan','Oceania','TV','TUV'],
            ['UG','Uganda','Ugandan','Africa','UG','UGA'],
            ['UA','Ukraine','Ukrainian','Europe','UA','UKR'],
            ['AE','United Arab Emirates','Emirati','Asia','AE','ARE'],
            ['GB','United Kingdom of Great Britain and Northern Ireland','British','Europe','GB','GBR'],
            ['US','United States of America','American','North America','US','USA'],
            ['UY','Uruguay','Uruguayan','South America','UY','URY'],
            ['UZ','Uzbekistan','Uzbekistani','Asia','UZ','UZB'],
            ['VU','Vanuatu','Ni-Vanuatu','Oceania','VU','VUT'],
            ['VE','Venezuela (Bolivarian Republic of)','Venezuelan','South America','VE','VEN'],
            ['VN','Viet Nam','Vietnamese','Asia','VN','VNM'],
            ['YE','Yemen','Yemeni','Asia','YE','YEM'],
            ['ZM','Zambia','Zambian','Africa','ZM','ZMB'],
            ['ZW','Zimbabwe','Zimbabwean','Africa','ZW','ZWE']
        ];
        
        $insertSql = "INSERT INTO countries (code, name, nationality, continent, iso2_code, iso3_code) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertSql);
        
        $inserted = 0;
        foreach ($countriesData as $country) {
            $stmt->execute($country);
            $inserted++;
        }
        
        echo "✅ Inserted $inserted countries successfully\n";
        return true;
        
    } catch (Exception $e) {
        echo "❌ Error creating countries table: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run migration if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    echo "Running countries table migration...\n";
    createCountriesTable();
    echo "Migration completed.\n";
}
