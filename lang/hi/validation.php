<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute को स्वीकार किया जाना चाहिए।',
    'accepted_if' => ':other :value होने पर :attribute को स्वीकार किया जाना चाहिए।',
    'active_url' => ':attribute एक वैध URL नहीं है।',
    'after' => ':attribute :date के बाद की तिथि होनी चाहिए।',
    'after_or_equal' => ':attribute :date के बाद या उसके बराबर की तिथि होनी चाहिए।',
    'alpha' => ':attribute में केवल अक्षर होने चाहिए।',
    'alpha_dash' => ':attribute में केवल अक्षर, संख्या, डैश और अंडरस्कोर होने चाहिए।',
    'alpha_num' => ':attribute में केवल अक्षर और संख्या होनी चाहिए।',
    'array' => ':attribute एक सरणी होनी चाहिए।',
    'before' => ':attribute :date से पहले की तिथि होनी चाहिए।',
    'before_or_equal' => ':attribute :date से पहले या उसके बराबर की तिथि होनी चाहिए।',
    'between' => [
        'numeric' => ':attribute :min और :max के बीच होना चाहिए।',
        'file' => ':attribute :min और :max किलोबाइट के बीच होना चाहिए।',
        'string' => ':attribute :min और :max वर्णों के बीच होना चाहिए।',
        'array' => ':attribute में :min और :max आइटम होने चाहिए।',
    ],
    'boolean' => ':attribute फ़ील्ड सही या गलत होना चाहिए।',
    'confirmed' => ':attribute पुष्टि मेल नहीं खाती।',
    'current_password' => 'पासवर्ड गलत है।',
    'date' => ':attribute एक वैध तिथि नहीं है।',
    'date_equals' => ':attribute :date के बराबर तिथि होनी चाहिए।',
    'date_format' => ':attribute :format प्रारूप से मेल नहीं खाता।',
    'declined' => ':attribute को अस्वीकार किया जाना चाहिए।',
    'declined_if' => ':other :value होने पर :attribute को अस्वीकार किया जाना चाहिए।',
    'different' => ':attribute और :other अलग होने चाहिए।',
    'digits' => ':attribute :digits अंक होने चाहिए।',
    'digits_between' => ':attribute :min और :max अंकों के बीच होना चाहिए।',
    'dimensions' => ':attribute में अमान्य छवि आयाम हैं।',
    'distinct' => ':attribute फ़ील्ड का डुप्लिकेट मान है।',
    'email' => ':attribute एक वैध ईमेल पता होना चाहिए।',
    'ends_with' => ':attribute निम्नलिखित में से एक के साथ समाप्त होना चाहिए: :values।',
    'enum' => 'चुना गया :attribute अमान्य है।',
    'exists' => 'चुना गया :attribute अमान्य है।',
    'file' => ':attribute एक फ़ाइल होनी चाहिए।',
    'filled' => ':attribute फ़ील्ड का मान होना चाहिए।',
    'gt' => [
        'numeric' => ':attribute :value से बड़ा होना चाहिए।',
        'file' => ':attribute :value किलोबाइट से बड़ा होना चाहिए।',
        'string' => ':attribute :value वर्णों से बड़ा होना चाहिए।',
        'array' => ':attribute में :value से अधिक आइटम होने चाहिए।',
    ],
    'gte' => [
        'numeric' => ':attribute :value से बड़ा या बराबर होना चाहिए।',
        'file' => ':attribute :value किलोबाइट से बड़ा या बराबर होना चाहिए।',
        'string' => ':attribute :value वर्णों से बड़ा या बराबर होना चाहिए।',
        'array' => ':attribute में :value या अधिक आइटम होने चाहिए।',
    ],
    'image' => ':attribute एक छवि होनी चाहिए।',
    'in' => 'चुना गया :attribute अमान्य है।',
    'in_array' => ':attribute फ़ील्ड :other में मौजूद नहीं है।',
    'integer' => ':attribute एक पूर्णांक होना चाहिए।',
    'ip' => ':attribute एक वैध IP पता होना चाहिए।',
    'ipv4' => ':attribute एक वैध IPv4 पता होना चाहिए।',
    'ipv6' => ':attribute एक वैध IPv6 पता होना चाहिए।',
    'json' => ':attribute एक वैध JSON स्ट्रिंग होनी चाहिए।',
    'lt' => [
        'numeric' => ':attribute :value से छोटा होना चाहिए।',
        'file' => ':attribute :value किलोबाइट से छोटा होना चाहिए।',
        'string' => ':attribute :value वर्णों से छोटा होना चाहिए।',
        'array' => ':attribute में :value से कम आइटम होने चाहिए।',
    ],
    'lte' => [
        'numeric' => ':attribute :value से छोटा या बराबर होना चाहिए।',
        'file' => ':attribute :value किलोबाइट से छोटा या बराबर होना चाहिए।',
        'string' => ':attribute :value वर्णों से छोटा या बराबर होना चाहिए।',
        'array' => ':attribute में :value या कम आइटम होने चाहिए।',
    ],
    'mac_address' => ':attribute एक वैध MAC पता होना चाहिए।',
    'max' => [
        'numeric' => ':attribute :max से बड़ा नहीं होना चाहिए।',
        'file' => ':attribute :max किलोबाइट से बड़ा नहीं होना चाहिए।',
        'string' => ':attribute :max वर्णों से बड़ा नहीं होना चाहिए।',
        'array' => ':attribute में :max से अधिक आइटम नहीं होने चाहिए।',
    ],
    'mimes' => ':attribute :values प्रकार की फ़ाइल होनी चाहिए।',
    'mimetypes' => ':attribute :values प्रकार की फ़ाइल होनी चाहिए।',
    'min' => [
        'numeric' => ':attribute कम से कम :min होना चाहिए।',
        'file' => ':attribute कम से कम :min किलोबाइट होना चाहिए।',
        'string' => ':attribute कम से कम :min वर्णों का होना चाहिए।',
        'array' => ':attribute में कम से कम :min आइटम होने चाहिए।',
    ],
    'multiple_of' => ':attribute :value का गुणक होना चाहिए।',
    'not_in' => 'चुना गया :attribute अमान्य है।',
    'not_regex' => ':attribute प्रारूप अमान्य है।',
    'numeric' => ':attribute एक संख्या होनी चाहिए।',
    'password' => 'पासवर्ड गलत है।',
    'present' => ':attribute फ़ील्ड मौजूद होना चाहिए।',
    'prohibited' => ':attribute फ़ील्ड निषिद्ध है।',
    'prohibited_if' => ':other :value होने पर :attribute फ़ील्ड निषिद्ध है।',
    'prohibited_unless' => ':other :values में नहीं होने पर :attribute फ़ील्ड निषिद्ध है।',
    'prohibits' => ':attribute फ़ील्ड :other मौजूद होने से रोकता है।',
    'regex' => ':attribute प्रारूप अमान्य है।',
    'required' => ':attribute फ़ील्ड आवश्यक है।',
    'required_array_keys' => ':attribute फ़ील्ड में :values के लिए प्रविष्टियां होनी चाहिए।',
    'required_if' => ':other :value होने पर :attribute फ़ील्ड आवश्यक है।',
    'required_unless' => ':other :values में नहीं होने पर :attribute फ़ील्ड आवश्यक है।',
    'required_with' => ':values मौजूद होने पर :attribute फ़ील्ड आवश्यक है।',
    'required_with_all' => ':values मौजूद होने पर :attribute फ़ील्ड आवश्यक है।',
    'required_without' => ':values मौजूद नहीं होने पर :attribute फ़ील्ड आवश्यक है।',
    'required_without_all' => ':values में से कोई भी मौजूद नहीं होने पर :attribute फ़ील्ड आवश्यक है।',
    'same' => ':attribute और :other मेल खाना चाहिए।',
    'size' => [
        'numeric' => ':attribute :size होना चाहिए।',
        'file' => ':attribute :size किलोबाइट होना चाहिए।',
        'string' => ':attribute :size वर्णों का होना चाहिए।',
        'array' => ':attribute में :size आइटम होने चाहिए।',
    ],
    'starts_with' => ':attribute निम्नलिखित में से एक के साथ शुरू होना चाहिए: :values।',
    'string' => ':attribute एक स्ट्रिंग होनी चाहिए।',
    'timezone' => ':attribute एक वैध क्षेत्र होना चाहिए।',
    'unique' => ':attribute पहले से लिया गया है।',
    'uploaded' => ':attribute अपलोड करने में विफल।',
    'url' => ':attribute एक वैध URL होना चाहिए।',
    'uuid' => ':attribute एक वैध UUID होना चाहिए।',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'कस्टम संदेश',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'नाम',
        'email' => 'ईमेल',
        'password' => 'पासवर्ड',
        'password_confirmation' => 'पासवर्ड पुष्टि',
        'title' => 'शीर्षक',
        'description' => 'विवरण',
        'content' => 'सामग्री',
        'status' => 'स्थिति',
        'active' => 'सक्रिय',
        'inactive' => 'निष्क्रिय',
        'created_at' => 'बनाया गया',
        'updated_at' => 'अपडेट किया गया',
    ],

];
