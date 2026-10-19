<?php

declare(strict_types=1);

namespace App\classes;

/**
 * CountryCatalog
 *
 * Provides a standardized catalog of 195+ sovereign countries with ISO 3166-1 alpha-2
 * codes, international dialling prefixes (E.164), and Unicode country flag emojis.
 *
 * Zero external dependencies, high-performance static arrays, fully immutable.
 */
class CountryCatalog
{
    /**
     * @var array<string, array{code: string, dial_code: string, flag: string, name: string}>
     */
    private static ?array $catalog = null;

    /**
     * Initialize and return the comprehensive country registry.
     *
     * @return array<string, array{code: string, dial_code: string, flag: string, name: string}>
     */
    public static function getAll(): array
    {
        if (self::$catalog !== null) {
            return self::$catalog;
        }

        self::$catalog = [
            'United Kingdom' => ['code' => 'GB', 'dial_code' => '+44', 'flag' => '🇬🇧', 'name' => 'United Kingdom'],
            'United States' => ['code' => 'US', 'dial_code' => '+1', 'flag' => '🇺🇸', 'name' => 'United States'],
            'Nigeria' => ['code' => 'NG', 'dial_code' => '+234', 'flag' => '🇳🇬', 'name' => 'Nigeria'],
            'Canada' => ['code' => 'CA', 'dial_code' => '+1', 'flag' => '🇨🇦', 'name' => 'Canada'],
            'Ghana' => ['code' => 'GH', 'dial_code' => '+233', 'flag' => '🇬🇭', 'name' => 'Ghana'],
            'South Africa' => ['code' => 'ZA', 'dial_code' => '+27', 'flag' => '🇿🇦', 'name' => 'South Africa'],
            'Kenya' => ['code' => 'KE', 'dial_code' => '+254', 'flag' => '🇰🇪', 'name' => 'Kenya'],
            'India' => ['code' => 'IN', 'dial_code' => '+91', 'flag' => '🇮🇳', 'name' => 'India'],
            'Jamaica' => ['code' => 'JM', 'dial_code' => '+1876', 'flag' => '🇯🇲', 'name' => 'Jamaica'],
            'Ireland' => ['code' => 'IE', 'dial_code' => '+353', 'flag' => '🇮🇪', 'name' => 'Ireland'],
            'Australia' => ['code' => 'AU', 'dial_code' => '+61', 'flag' => '🇦🇺', 'name' => 'Australia'],
            'New Zealand' => ['code' => 'NZ', 'dial_code' => '+64', 'flag' => '🇳🇿', 'name' => 'New Zealand'],
            'France' => ['code' => 'FR', 'dial_code' => '+33', 'flag' => '🇫🇷', 'name' => 'France'],
            'Germany' => ['code' => 'DE', 'dial_code' => '+49', 'flag' => '🇩🇪', 'name' => 'Germany'],
            'Spain' => ['code' => 'ES', 'dial_code' => '+34', 'flag' => '🇪🇸', 'name' => 'Spain'],
            'Italy' => ['code' => 'IT', 'dial_code' => '+39', 'flag' => '🇮🇹', 'name' => 'Italy'],
            'Netherlands' => ['code' => 'NL', 'dial_code' => '+31', 'flag' => '🇳🇱', 'name' => 'Netherlands'],
            'Belgium' => ['code' => 'BE', 'dial_code' => '+32', 'flag' => '🇧🇪', 'name' => 'Belgium'],
            'Portugal' => ['code' => 'PT', 'dial_code' => '+351', 'flag' => '🇵🇹', 'name' => 'Portugal'],
            'Sweden' => ['code' => 'SE', 'dial_code' => '+46', 'flag' => '🇸🇪', 'name' => 'Sweden'],
            'Switzerland' => ['code' => 'CH', 'dial_code' => '+41', 'flag' => '🇨🇭', 'name' => 'Switzerland'],
            'Brazil' => ['code' => 'BR', 'dial_code' => '+55', 'flag' => '🇧🇷', 'name' => 'Brazil'],
            'Mexico' => ['code' => 'MX', 'dial_code' => '+52', 'flag' => '🇲🇽', 'name' => 'Mexico'],
            'Trinidad and Tobago' => ['code' => 'TT', 'dial_code' => '+1868', 'flag' => '🇹🇹', 'name' => 'Trinidad and Tobago'],
            'Barbados' => ['code' => 'BB', 'dial_code' => '+1246', 'flag' => '🇧🇧', 'name' => 'Barbados'],
            'United Arab Emirates' => ['code' => 'AE', 'dial_code' => '+971', 'flag' => '🇦🇪', 'name' => 'United Arab Emirates'],
            'Saudi Arabia' => ['code' => 'SA', 'dial_code' => '+966', 'flag' => '🇸🇦', 'name' => 'Saudi Arabia'],
            'Singapore' => ['code' => 'SG', 'dial_code' => '+65', 'flag' => '🇸🇬', 'name' => 'Singapore'],
            'Malaysia' => ['code' => 'MY', 'dial_code' => '+60', 'flag' => '🇲🇾', 'name' => 'Malaysia'],
            'Pakistan' => ['code' => 'PK', 'dial_code' => '+92', 'flag' => '🇵🇰', 'name' => 'Pakistan'],
            'Bangladesh' => ['code' => 'BD', 'dial_code' => '+880', 'flag' => '🇧🇩', 'name' => 'Bangladesh'],
            'Sierra Leone' => ['code' => 'SL', 'dial_code' => '+232', 'flag' => '🇸🇱', 'name' => 'Sierra Leone'],
            'Uganda' => ['code' => 'UG', 'dial_code' => '+256', 'flag' => '🇺🇬', 'name' => 'Uganda'],
            'Tanzania' => ['code' => 'TZ', 'dial_code' => '+255', 'flag' => '🇹🇿', 'name' => 'Tanzania'],
            'Rwanda' => ['code' => 'RW', 'dial_code' => '+250', 'flag' => '🇷🇼', 'name' => 'Rwanda'],
            'Zimbabwe' => ['code' => 'ZW', 'dial_code' => '+263', 'flag' => '🇿🇼', 'name' => 'Zimbabwe'],
            'Zambia' => ['code' => 'ZM', 'dial_code' => '+260', 'flag' => '🇿🇲', 'name' => 'Zambia'],
            'Botswana' => ['code' => 'BW', 'dial_code' => '+267', 'flag' => '🇧🇼', 'name' => 'Botswana'],
            'Namibia' => ['code' => 'NA', 'dial_code' => '+264', 'flag' => '🇳🇦', 'name' => 'Namibia'],
            'Cameroon' => ['code' => 'CM', 'dial_code' => '+237', 'flag' => '🇨🇲', 'name' => 'Cameroon'],
            'Côte d\'Ivoire' => ['code' => 'CI', 'dial_code' => '+225', 'flag' => '🇨🇮', 'name' => 'Côte d\'Ivoire'],
            'Senegal' => ['code' => 'SN', 'dial_code' => '+221', 'flag' => '🇸🇳', 'name' => 'Senegal'],
            'Egypt' => ['code' => 'EG', 'dial_code' => '+20', 'flag' => '🇪🇬', 'name' => 'Egypt'],
            'Morocco' => ['code' => 'MA', 'dial_code' => '+212', 'flag' => '🇲🇦', 'name' => 'Morocco'],
            'China' => ['code' => 'CN', 'dial_code' => '+86', 'flag' => '🇨🇳', 'name' => 'China'],
            'Japan' => ['code' => 'JP', 'dial_code' => '+81', 'flag' => '🇯🇵', 'name' => 'Japan'],
            'South Korea' => ['code' => 'KR', 'dial_code' => '+82', 'flag' => '🇰🇷', 'name' => 'South Korea'],
            'Philippines' => ['code' => 'PH', 'dial_code' => '+63', 'flag' => '🇵🇭', 'name' => 'Philippines'],
            'Poland' => ['code' => 'PL', 'dial_code' => '+48', 'flag' => '🇵🇱', 'name' => 'Poland'],
            'Norway' => ['code' => 'NO', 'dial_code' => '+47', 'flag' => '🇳🇴', 'name' => 'Norway'],
            'Denmark' => ['code' => 'DK', 'dial_code' => '+45', 'flag' => '🇩🇰', 'name' => 'Denmark'],
            'Finland' => ['code' => 'FI', 'dial_code' => '+358', 'flag' => '🇫🇮', 'name' => 'Finland'],
            'Austria' => ['code' => 'AT', 'dial_code' => '+43', 'flag' => '🇦🇹', 'name' => 'Austria'],
            'Greece' => ['code' => 'GR', 'dial_code' => '+30', 'flag' => '🇬🇷', 'name' => 'Greece'],
            'Cyprus' => ['code' => 'CY', 'dial_code' => '+357', 'flag' => '🇨🇾', 'name' => 'Cyprus'],
            'Czech Republic' => ['code' => 'CZ', 'dial_code' => '+420', 'flag' => '🇨🇿', 'name' => 'Czech Republic'],
            'Hungary' => ['code' => 'HU', 'dial_code' => '+36', 'flag' => '🇭🇺', 'name' => 'Hungary'],
            'Romania' => ['code' => 'RO', 'dial_code' => '+40', 'flag' => '🇷🇴', 'name' => 'Romania'],
            'Ukraine' => ['code' => 'UA', 'dial_code' => '+380', 'flag' => '🇺🇦', 'name' => 'Ukraine'],
            'Turkey' => ['code' => 'TR', 'dial_code' => '+90', 'flag' => '🇹🇷', 'name' => 'Turkey'],
            'Israel' => ['code' => 'IL', 'dial_code' => '+972', 'flag' => '🇮🇱', 'name' => 'Israel'],
            'Qatar' => ['code' => 'QA', 'dial_code' => '+974', 'flag' => '🇶🇦', 'name' => 'Qatar'],
            'Kuwait' => ['code' => 'KW', 'dial_code' => '+965', 'flag' => '🇰🇼', 'name' => 'Kuwait'],
            'Oman' => ['code' => 'OM', 'dial_code' => '+968', 'flag' => '🇴🇲', 'name' => 'Oman'],
            'Bahrain' => ['code' => 'BH', 'dial_code' => '+973', 'flag' => '🇧🇭', 'name' => 'Bahrain'],
            'Argentina' => ['code' => 'AR', 'dial_code' => '+54', 'flag' => '🇦🇷', 'name' => 'Argentina'],
            'Chile' => ['code' => 'CL', 'dial_code' => '+56', 'flag' => '🇨🇱', 'name' => 'Chile'],
            'Colombia' => ['code' => 'CO', 'dial_code' => '+57', 'flag' => '🇨🇴', 'name' => 'Colombia'],
            'Peru' => ['code' => 'PE', 'dial_code' => '+51', 'flag' => '🇵🇪', 'name' => 'Peru'],
            'Guyana' => ['code' => 'GY', 'dial_code' => '+592', 'flag' => '🇬🇾', 'name' => 'Guyana'],
            'Grenada' => ['code' => 'GD', 'dial_code' => '+1473', 'flag' => '🇬🇩', 'name' => 'Grenada'],
            'Saint Lucia' => ['code' => 'LC', 'dial_code' => '+1758', 'flag' => '🇱🇨', 'name' => 'Saint Lucia'],
            'Saint Vincent and the Grenadines' => ['code' => 'VC', 'dial_code' => '+1784', 'flag' => '🇻🇨', 'name' => 'Saint Vincent and the Grenadines'],
            'Dominica' => ['code' => 'DM', 'dial_code' => '+1767', 'flag' => '🇩🇲', 'name' => 'Dominica'],
            'Bahamas' => ['code' => 'BS', 'dial_code' => '+1242', 'flag' => '🇧🇸', 'name' => 'Bahamas'],
            'Antigua and Barbuda' => ['code' => 'AG', 'dial_code' => '+1268', 'flag' => '🇦🇬', 'name' => 'Antigua and Barbuda'],
            'Saint Kitts and Nevis' => ['code' => 'KN', 'dial_code' => '+1869', 'flag' => '🇰🇳', 'name' => 'Saint Kitts and Nevis'],
            'Belize' => ['code' => 'BZ', 'dial_code' => '+501', 'flag' => '🇧🇿', 'name' => 'Belize'],
            'Afghanistan' => ['code' => 'AF', 'dial_code' => '+93', 'flag' => '🇦🇫', 'name' => 'Afghanistan'],
            'Albania' => ['code' => 'AL', 'dial_code' => '+355', 'flag' => '🇦🇱', 'name' => 'Albania'],
            'Algeria' => ['code' => 'DZ', 'dial_code' => '+213', 'flag' => '🇩🇿', 'name' => 'Algeria'],
            'Andorra' => ['code' => 'AD', 'dial_code' => '+376', 'flag' => '🇦🇩', 'name' => 'Andorra'],
            'Angola' => ['code' => 'AO', 'dial_code' => '+244', 'flag' => '🇦🇴', 'name' => 'Angola'],
            'Armenia' => ['code' => 'AM', 'dial_code' => '+374', 'flag' => '🇦🇲', 'name' => 'Armenia'],
            'Azerbaijan' => ['code' => 'AZ', 'dial_code' => '+994', 'flag' => '🇦🇿', 'name' => 'Azerbaijan'],
            'Belarus' => ['code' => 'BY', 'dial_code' => '+375', 'flag' => '🇧🇾', 'name' => 'Belarus'],
            'Benin' => ['code' => 'BJ', 'dial_code' => '+229', 'flag' => '🇧🇯', 'name' => 'Benin'],
            'Bhutan' => ['code' => 'BT', 'dial_code' => '+975', 'flag' => '🇧🇹', 'name' => 'Bhutan'],
            'Bolivia' => ['code' => 'BO', 'dial_code' => '+591', 'flag' => '🇧🇴', 'name' => 'Bolivia'],
            'Bosnia and Herzegovina' => ['code' => 'BA', 'dial_code' => '+387', 'flag' => '🇧🇦', 'name' => 'Bosnia and Herzegovina'],
            'Brunei' => ['code' => 'BN', 'dial_code' => '+673', 'flag' => '🇧🇳', 'name' => 'Brunei'],
            'Bulgaria' => ['code' => 'BG', 'dial_code' => '+359', 'flag' => '🇧🇬', 'name' => 'Bulgaria'],
            'Burkina Faso' => ['code' => 'BF', 'dial_code' => '+226', 'flag' => '🇧🇫', 'name' => 'Burkina Faso'],
            'Burundi' => ['code' => 'BI', 'dial_code' => '+257', 'flag' => '🇧🇮', 'name' => 'Burundi'],
            'Cambodia' => ['code' => 'KH', 'dial_code' => '+855', 'flag' => '🇰🇭', 'name' => 'Cambodia'],
            'Cape Verde' => ['code' => 'CV', 'dial_code' => '+238', 'flag' => '🇨🇻', 'name' => 'Cape Verde'],
            'Central African Republic' => ['code' => 'CF', 'dial_code' => '+236', 'flag' => '🇨🇫', 'name' => 'Central African Republic'],
            'Chad' => ['code' => 'TD', 'dial_code' => '+235', 'flag' => '🇹🇩', 'name' => 'Chad'],
            'Comoros' => ['code' => 'KM', 'dial_code' => '+269', 'flag' => '🇰🇲', 'name' => 'Comoros'],
            'Congo (Brazzaville)' => ['code' => 'CG', 'dial_code' => '+242', 'flag' => '🇨🇬', 'name' => 'Congo (Brazzaville)'],
            'Congo (Kinshasa)' => ['code' => 'CD', 'dial_code' => '+243', 'flag' => '🇨🇩', 'name' => 'Congo (Kinshasa)'],
            'Costa Rica' => ['code' => 'CR', 'dial_code' => '+506', 'flag' => '🇨🇷', 'name' => 'Costa Rica'],
            'Croatia' => ['code' => 'HR', 'dial_code' => '+385', 'flag' => '🇭🇷', 'name' => 'Croatia'],
            'Cuba' => ['code' => 'CU', 'dial_code' => '+53', 'flag' => '🇨🇺', 'name' => 'Cuba'],
            'Djibouti' => ['code' => 'DJ', 'dial_code' => '+253', 'flag' => '🇩🇯', 'name' => 'Djibouti'],
            'Dominican Republic' => ['code' => 'DO', 'dial_code' => '+1809', 'flag' => '🇩🇴', 'name' => 'Dominican Republic'],
            'East Timor' => ['code' => 'TL', 'dial_code' => '+670', 'flag' => '🇹🇱', 'name' => 'East Timor'],
            'Ecuador' => ['code' => 'EC', 'dial_code' => '+593', 'flag' => '🇪🇨', 'name' => 'Ecuador'],
            'El Salvador' => ['code' => 'SV', 'dial_code' => '+503', 'flag' => '🇸🇻', 'name' => 'El Salvador'],
            'Equatorial Guinea' => ['code' => 'GQ', 'dial_code' => '+240', 'flag' => '🇬🇶', 'name' => 'Equatorial Guinea'],
            'Eritrea' => ['code' => 'ER', 'dial_code' => '+291', 'flag' => '🇪🇷', 'name' => 'Eritrea'],
            'Estonia' => ['code' => 'EE', 'dial_code' => '+372', 'flag' => '🇪🇪', 'name' => 'Estonia'],
            'Ethiopia' => ['code' => 'ET', 'dial_code' => '+251', 'flag' => '🇪🇹', 'name' => 'Ethiopia'],
            'Fiji' => ['code' => 'FJ', 'dial_code' => '+679', 'flag' => '🇫🇯', 'name' => 'Fiji'],
            'Gabon' => ['code' => 'GA', 'dial_code' => '+241', 'flag' => '🇬🇦', 'name' => 'Gabon'],
            'Gambia' => ['code' => 'GM', 'dial_code' => '+220', 'flag' => '🇬🇲', 'name' => 'Gambia'],
            'Georgia' => ['code' => 'GE', 'dial_code' => '+995', 'flag' => '🇬🇪', 'name' => 'Georgia'],
            'Guatemala' => ['code' => 'GT', 'dial_code' => '+502', 'flag' => '🇬🇹', 'name' => 'Guatemala'],
            'Guinea' => ['code' => 'GN', 'dial_code' => '+224', 'flag' => '🇬🇳', 'name' => 'Guinea'],
            'Guinea-Bissau' => ['code' => 'GW', 'dial_code' => '+245', 'flag' => '🇬🇼', 'name' => 'Guinea-Bissau'],
            'Haiti' => ['code' => 'HT', 'dial_code' => '+509', 'flag' => '🇭🇹', 'name' => 'Haiti'],
            'Honduras' => ['code' => 'HN', 'dial_code' => '+504', 'flag' => '🇭🇳', 'name' => 'Honduras'],
            'Iceland' => ['code' => 'IS', 'dial_code' => '+354', 'flag' => '🇮🇸', 'name' => 'Iceland'],
            'Indonesia' => ['code' => 'ID', 'dial_code' => '+62', 'flag' => '🇮🇩', 'name' => 'Indonesia'],
            'Iran' => ['code' => 'IR', 'dial_code' => '+98', 'flag' => '🇮🇷', 'name' => 'Iran'],
            'Iraq' => ['code' => 'IQ', 'dial_code' => '+964', 'flag' => '🇮🇶', 'name' => 'Iraq'],
            'Jordan' => ['code' => 'JO', 'dial_code' => '+962', 'flag' => '🇯🇴', 'name' => 'Jordan'],
            'Kazakhstan' => ['code' => 'KZ', 'dial_code' => '+7', 'flag' => '🇰🇿', 'name' => 'Kazakhstan'],
            'Kiribati' => ['code' => 'KI', 'dial_code' => '+686', 'flag' => '🇰🇮', 'name' => 'Kiribati'],
            'North Korea' => ['code' => 'KP', 'dial_code' => '+850', 'flag' => '🇰🇵', 'name' => 'North Korea'],
            'Kosovo' => ['code' => 'XK', 'dial_code' => '+383', 'flag' => '🇽🇰', 'name' => 'Kosovo'],
            'Kyrgyzstan' => ['code' => 'KG', 'dial_code' => '+996', 'flag' => '🇰🇬', 'name' => 'Kyrgyzstan'],
            'Laos' => ['code' => 'LA', 'dial_code' => '+856', 'flag' => '🇱🇦', 'name' => 'Laos'],
            'Latvia' => ['code' => 'LV', 'dial_code' => '+371', 'flag' => '🇱🇻', 'name' => 'Latvia'],
            'Lebanon' => ['code' => 'LB', 'dial_code' => '+961', 'flag' => '🇱🇧', 'name' => 'Lebanon'],
            'Lesotho' => ['code' => 'LS', 'dial_code' => '+266', 'flag' => '🇱🇸', 'name' => 'Lesotho'],
            'Liberia' => ['code' => 'LR', 'dial_code' => '+231', 'flag' => '🇱🇷', 'name' => 'Liberia'],
            'Libya' => ['code' => 'LY', 'dial_code' => '+218', 'flag' => '🇱🇾', 'name' => 'Libya'],
            'Liechtenstein' => ['code' => 'LI', 'dial_code' => '+423', 'flag' => '🇱🇮', 'name' => 'Liechtenstein'],
            'Lithuania' => ['code' => 'LT', 'dial_code' => '+370', 'flag' => '🇱🇹', 'name' => 'Lithuania'],
            'Luxembourg' => ['code' => 'LU', 'dial_code' => '+352', 'flag' => '🇱🇺', 'name' => 'Luxembourg'],
            'Macedonia' => ['code' => 'MK', 'dial_code' => '+389', 'flag' => '🇲🇰', 'name' => 'Macedonia'],
            'Madagascar' => ['code' => 'MG', 'dial_code' => '+261', 'flag' => '🇲🇬', 'name' => 'Madagascar'],
            'Malawi' => ['code' => 'MW', 'dial_code' => '+265', 'flag' => '🇲🇼', 'name' => 'Malawi'],
            'Maldives' => ['code' => 'MV', 'dial_code' => '+960', 'flag' => '🇲🇻', 'name' => 'Maldives'],
            'Mali' => ['code' => 'ML', 'dial_code' => '+223', 'flag' => '🇲🇱', 'name' => 'Mali'],
            'Malta' => ['code' => 'MT', 'dial_code' => '+356', 'flag' => '🇲🇹', 'name' => 'Malta'],
            'Marshall Islands' => ['code' => 'MH', 'dial_code' => '+692', 'flag' => '🇲🇭', 'name' => 'Marshall Islands'],
            'Mauritania' => ['code' => 'MR', 'dial_code' => '+222', 'flag' => '🇲🇷', 'name' => 'Mauritania'],
            'Mauritius' => ['code' => 'MU', 'dial_code' => '+230', 'flag' => '🇲🇺', 'name' => 'Mauritius'],
            'Micronesia' => ['code' => 'FM', 'dial_code' => '+691', 'flag' => '🇫🇲', 'name' => 'Micronesia'],
            'Moldova' => ['code' => 'MD', 'dial_code' => '+373', 'flag' => '🇲🇩', 'name' => 'Moldova'],
            'Monaco' => ['code' => 'MC', 'dial_code' => '+377', 'flag' => '🇲🇨', 'name' => 'Monaco'],
            'Mongolia' => ['code' => 'MN', 'dial_code' => '+976', 'flag' => '🇲🇳', 'name' => 'Mongolia'],
            'Montenegro' => ['code' => 'ME', 'dial_code' => '+382', 'flag' => '🇲🇪', 'name' => 'Montenegro'],
            'Mozambique' => ['code' => 'MZ', 'dial_code' => '+258', 'flag' => '🇲🇿', 'name' => 'Mozambique'],
            'Myanmar' => ['code' => 'MM', 'dial_code' => '+95', 'flag' => '🇲🇲', 'name' => 'Myanmar'],
            'Nauru' => ['code' => 'NR', 'dial_code' => '+674', 'flag' => '🇳🇷', 'name' => 'Nauru'],
            'Nepal' => ['code' => 'NP', 'dial_code' => '+977', 'flag' => '🇳🇵', 'name' => 'Nepal'],
            'Nicaragua' => ['code' => 'NI', 'dial_code' => '+505', 'flag' => '🇳🇮', 'name' => 'Nicaragua'],
            'Niger' => ['code' => 'NE', 'dial_code' => '+227', 'flag' => '🇳🇪', 'name' => 'Niger'],
            'Pakistan' => ['code' => 'PK', 'dial_code' => '+92', 'flag' => '🇵🇰', 'name' => 'Pakistan'],
            'Palau' => ['code' => 'PW', 'dial_code' => '+680', 'flag' => '🇵🇼', 'name' => 'Palau'],
            'Panama' => ['code' => 'PA', 'dial_code' => '+507', 'flag' => '🇵🇦', 'name' => 'Panama'],
            'Papua New Guinea' => ['code' => 'PG', 'dial_code' => '+675', 'flag' => '🇵🇬', 'name' => 'Papua New Guinea'],
            'Paraguay' => ['code' => 'PY', 'dial_code' => '+595', 'flag' => '🇵🇾', 'name' => 'Paraguay'],
            'Russia' => ['code' => 'RU', 'dial_code' => '+7', 'flag' => '🇷🇺', 'name' => 'Russia'],
            'Samoa' => ['code' => 'WS', 'dial_code' => '+685', 'flag' => '🇼🇸', 'name' => 'Samoa'],
            'San Marino' => ['code' => 'SM', 'dial_code' => '+378', 'flag' => '🇸🇲', 'name' => 'San Marino'],
            'Sao Tome and Principe' => ['code' => 'ST', 'dial_code' => '+239', 'flag' => '🇸🇹', 'name' => 'Sao Tome and Principe'],
            'Serbia' => ['code' => 'RS', 'dial_code' => '+381', 'flag' => '🇷🇸', 'name' => 'Serbia'],
            'Seychelles' => ['code' => 'SC', 'dial_code' => '+248', 'flag' => '🇸🇨', 'name' => 'Seychelles'],
            'Slovakia' => ['code' => 'SK', 'dial_code' => '+421', 'flag' => '🇸🇰', 'name' => 'Slovakia'],
            'Slovenia' => ['code' => 'SI', 'dial_code' => '+386', 'flag' => '🇸🇮', 'name' => 'Slovenia'],
            'Solomon Islands' => ['code' => 'SB', 'dial_code' => '+677', 'flag' => '🇸🇧', 'name' => 'Solomon Islands'],
            'Somalia' => ['code' => 'SO', 'dial_code' => '+252', 'flag' => '🇸🇴', 'name' => 'Somalia'],
            'South Sudan' => ['code' => 'SS', 'dial_code' => '+211', 'flag' => '🇸🇸', 'name' => 'South Sudan'],
            'Sri Lanka' => ['code' => 'LK', 'dial_code' => '+94', 'flag' => '🇱🇰', 'name' => 'Sri Lanka'],
            'Sudan' => ['code' => 'SD', 'dial_code' => '+249', 'flag' => '🇸🇩', 'name' => 'Sudan'],
            'Suriname' => ['code' => 'SR', 'dial_code' => '+597', 'flag' => '🇸🇷', 'name' => 'Suriname'],
            'Swaziland' => ['code' => 'SZ', 'dial_code' => '+268', 'flag' => '🇸🇿', 'name' => 'Swaziland'],
            'Syria' => ['code' => 'SY', 'dial_code' => '+963', 'flag' => '🇸🇾', 'name' => 'Syria'],
            'Taiwan' => ['code' => 'TW', 'dial_code' => '+886', 'flag' => '🇹🇼', 'name' => 'Taiwan'],
            'Tajikistan' => ['code' => 'TJ', 'dial_code' => '+992', 'flag' => '🇹🇯', 'name' => 'Tajikistan'],
            'Thailand' => ['code' => 'TH', 'dial_code' => '+66', 'flag' => '🇹🇭', 'name' => 'Thailand'],
            'Togo' => ['code' => 'TG', 'dial_code' => '+228', 'flag' => '🇹🇬', 'name' => 'Togo'],
            'Tonga' => ['code' => 'TO', 'dial_code' => '+676', 'flag' => '🇹🇴', 'name' => 'Tonga'],
            'Tunisia' => ['code' => 'TN', 'dial_code' => '+216', 'flag' => '🇹🇳', 'name' => 'Tunisia'],
            'Turkmenistan' => ['code' => 'TM', 'dial_code' => '+993', 'flag' => '🇹🇲', 'name' => 'Turkmenistan'],
            'Tuvalu' => ['code' => 'TV', 'dial_code' => '+688', 'flag' => '🇹🇻', 'name' => 'Tuvalu'],
            'Uruguay' => ['code' => 'UY', 'dial_code' => '+598', 'flag' => '🇺🇾', 'name' => 'Uruguay'],
            'Uzbekistan' => ['code' => 'UZ', 'dial_code' => '+998', 'flag' => '🇺🇿', 'name' => 'Uzbekistan'],
            'Vanuatu' => ['code' => 'VU', 'dial_code' => '+678', 'flag' => '🇻🇺', 'name' => 'Vanuatu'],
            'Vatican City' => ['code' => 'VA', 'dial_code' => '+379', 'flag' => '🇻🇦', 'name' => 'Vatican City'],
            'Venezuela' => ['code' => 'VE', 'dial_code' => '+58', 'flag' => '🇻🇪', 'name' => 'Venezuela'],
            'Vietnam' => ['code' => 'VN', 'dial_code' => '+84', 'flag' => '🇻🇳', 'name' => 'Vietnam'],
            'Yemen' => ['code' => 'YE', 'dial_code' => '+967', 'flag' => '🇾🇪', 'name' => 'Yemen'],
        ];

        return self::$catalog;
    }

    /**
     * Retrieve country metadata by country name or 2-letter ISO code.
     *
     * @param string $nameOrCode
     * @return array{code: string, dial_code: string, flag: string, name: string}|null
     */
    public static function find(string $nameOrCode): ?array
    {
        $catalog = self::getAll();
        $trimmed = trim($nameOrCode);
        if ($trimmed === '') {
            return null;
        }

        if (isset($catalog[$trimmed])) {
            return $catalog[$trimmed];
        }

        $upper = strtoupper($trimmed);
        foreach ($catalog as $item) {
            if ($item['code'] === $upper || strcasecmp($item['name'], $trimmed) === 0) {
                return $item;
            }
        }

        // Common aliases
        $aliases = [
            'UK' => 'United Kingdom',
            'GB' => 'United Kingdom',
            'GREAT BRITAIN' => 'United Kingdom',
            'ENGLAND' => 'United Kingdom',
            'SCOTLAND' => 'United Kingdom',
            'WALES' => 'United Kingdom',
            'USA' => 'United States',
            'US' => 'United States',
            'AMERICA' => 'United States',
            'UAE' => 'United Arab Emirates',
        ];

        if (isset($aliases[$upper]) && isset($catalog[$aliases[$upper]])) {
            return $catalog[$aliases[$upper]];
        }

        return null;
    }

    /**
     * Get dial code for a given country (fallback to empty string if unknown).
     */
    public static function getDialCode(string $nameOrCode): string
    {
        $country = self::find($nameOrCode);
        return $country['dial_code'] ?? '';
    }

    /**
     * Get flag emoji for a given country (fallback to 🌐 if unknown).
     */
    public static function getFlag(string $nameOrCode): string
    {
        $country = self::find($nameOrCode);
        return $country['flag'] ?? '🌐';
    }
}
