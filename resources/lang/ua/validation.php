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

    'accepted'             => 'Атрибут: повинен бути прийнятий.',
    'active_url'           => 'Атрибут: не є дійсною URL-адресою.',
    'after'                => 'Атрибут: повинен бути датою після: дата.',
    'after_or_equal'       => 'Атрибут: повинен бути датою після або датою: дата.',
    'alpha'                => 'Атрибут: може містити лише літери.',
    'alpha_dash'           => 'Атрибут: може містити лише літери, цифри та тире.',
    'alpha_num'            => 'Атрибут: може містити лише літери та цифри.',
    'array'                => 'Атрибут: повинен бути масивом.',
    'before'               => 'Атрибут: повинен бути датою до: дата.',
    'before_or_equal'      => 'В атрибуті: атрибут повинен бути датою до дати або дорівнює: даті.',
    'between'              => [
        'numeric' => 'Атрибут: повинен бути між: min та: max.',
        'file'    => 'Атрибут: повинен бути між: min та: max кілобайт.',
        'string'  => 'Атрибут: повинен бути між: min та: max символами.',
        'array'   => 'Атрибут: повинен містити між: min та: max елементів.',
    ],
    'boolean'              => 'Поле: атрибут має бути істинним або хибним.',
    'confirmed'            => 'Підтвердження атрибута не відповідає.',
    'date'                 => 'Атрибут: не є дійсною датою.',
    'date_format'          => 'Атрибут: не відповідає формату: формат.',
    'different'            => 'Атрибут: та інші повинні бути різними.',
    'digits'               => 'Атрибут: повинен бути: цифри цифр.',
    'digits_between'       => 'Атрибут: повинен бути між: min та: max цифрами.',
    'dimensions'           => 'Атрибут: має недійсні розміри зображення.',
    'distinct'             => 'Атрибут: поле має повторюване значення.',
    'email'                => 'Атрибут: повинен бути дійсною адресою електронної пошти.',
    'exists'               => 'Вибраний: атрибут недійсний.',
    'file'                 => 'Атрибут: повинен бути файлом.',
    'filled'               => 'Поле: атрибут обов’язкове.',
    'image'                => 'Атрибут: повинен бути зображенням.',
    'in'                   => 'Вибраний: атрибут недійсний.',
    'in_array'             => 'Поле: атрибут не існує в: інших.',
    'integer'              => 'Атрибут: повинен бути цілим числом.',
    'ip'                   => 'Атрибут: повинен бути дійсною IP-адресою.',
    'json'                 => 'Атрибут: повинен бути дійсним рядком JSON.',
    'max'                  => [
        'numeric' => 'Атрибут: не може перевищувати: макс.',
        'file'    => 'Атрибут: не може перевищувати: макс. Кілобайт.',
        'string'  => 'Атрибут: не може перевищувати: максимум символів.',
        'array'   => 'В атрибуті: може бути не більше: максимум елементів.',
    ],
    'mimes'                => 'Атрибут: повинен бути файлом типу: значень.',
    'mimetypes'            => 'Атрибут: повинен бути файлом типу: значень.',
    'min'                  => [
        'numeric' => 'Атрибут: повинен бути не менше: мінімум.',
        'file'    => 'Атрибут: повинен бути не менше: мінімум кілобайт.',
        'string'  => 'Атрибут: повинен бути не менше: мінімум символів.',
        'array'   => 'Атрибут: повинен містити щонайменше: мінімум елементів.',
    ],
    'not_in'               => 'Вибраний: атрибут недійсний.',
    'numeric'              => 'Атрибут: повинен бути числом.',
    'present'              => 'Поле: атрибут має бути присутнім.',
    'regex'                => 'Формат атрибутів недійсний.',
    'required'             => 'Поле: атрибут обов’язкове.',
    'required_if'          => 'Поле: атрибут обов’язкове, коли: інше: значення.',
    'required_unless'      => 'Поле: атрибут обов’язкове, якщо: інше не вказано у: значеннях.',
    'required_with'        => 'Поле: атрибут обов’язкове, коли: значення є.',
    'required_with_all'    => 'Поле: атрибут обов’язкове, коли: значення є.',
    'required_without'     => 'Поле: атрибут обов’язкове, коли: значень немає.',
    'required_without_all' => 'Поле: атрибут обов’язкове, коли жодне з: значень немає.',
    'same'                 => 'Атрибут: та інше повинні відповідати.',
    'size'                 => [
        'numeric' => 'Атрибут: повинен бути: розмір.',
        'file'    => 'Атрибут: повинен бути: розмір кілобайт.',
        'string'  => 'Атрибут: повинен бути: розмір символів.',
        'array'   => 'Атрибут: повинен містити: елементи розміру.',
    ],
    'string'               => 'Атрибут: повинен бути рядком.',
    'timezone'             => 'Атрибут: повинен бути дійсною зоною.',
    'unique'               => 'Атрибут: вже взято.',
    'uploaded'             => 'Не вдалося завантажити атрибут.',
    'url'                  => 'Формат атрибутів недійсний.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
