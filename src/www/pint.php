<?php

/**
 * Laravel Pint設定ファイル
 * コードスタイルフォーマッターの詳細設定
 */
return [
    // Laravel推奨のコーディングスタイルをベースにする（PSR-12準拠）
    'preset' => 'laravel',

    'rules' => [
        // 配列定義に短い構文 [] を使用（array()ではなく）
        'array_syntax' => [
            'syntax' => 'short',
        ],

        // useステートメントをアルファベット順に並べ替え
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],

        // 未使用のuseステートメントを削除
        'no_unused_imports' => true,

        // namespace宣言の後に空白行を挿入
        'blank_line_after_namespace' => true,

        // PHPの開始タグ（<?php）の後に空白行を挿入
        'blank_line_after_opening_tag' => true,

        // 特定のステートメント（return, try, throw, if）の前に空白行を挿入
        'blank_line_before_statement' => [
            'statements' => ['return', 'try', 'throw', 'if'],
        ],

        // クラス内の要素（メソッド、プロパティ）の間に1行の空白を挿入
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],

        // Nullable型ヒント（?string）の間にスペースを入れない
        'compact_nullable_typehint' => true,

        // declare文の等号の前後に1つのスペースを入れる
        'declare_equal_normalize' => [
            'space' => 'single',
        ],

        // 型キャスト演算子を小文字で記述（例：(int)$foo）
        'lowercase_cast' => true,

        // staticキーワードを小文字で記述
        'lowercase_static_reference' => true,

        // newキーワードの後に括弧を使用（例：new Class()）
        'new_with_braces' => true,

        // クラス宣言の開始括弧の後の空白行を削除
        'no_blank_lines_after_class_opening' => true,

        // useステートメントの先頭のバックスラッシュを削除
        'no_leading_import_slash' => true,

        // 空白行内の余分な空白を削除
        'no_whitespace_in_blank_line' => true,

        // クラス内の要素を指定された順序に並べ替え
        'ordered_class_elements' => [
            // 1. トレイト使用宣言
            'use_trait',
            // 2. 定数（public, protected, private）
            'constant_public',
            'constant_protected',
            'constant_private',
            // 3. プロパティ（public, protected, private）
            'property_public',
            'property_protected',
            'property_private',
            // 4. コンストラクタ
            'construct',
            // 5. メソッド（public, protected, private）
            'method_public',
            'method_protected',
            'method_private',
        ],

        // 戻り値の型宣言の前にスペースを入れない
        'return_type_declaration' => [
            'space_before' => 'none',
        ],

        // 複数のトレイトを使用する場合、各トレイトを別々のuseステートメントで宣言
        'single_trait_insert_per_statement' => true,

        // 三項演算子の周りにスペースを入れる
        'ternary_operator_spaces' => true,

        // クラスの定数、プロパティ、メソッドにはアクセス修飾子（public, protected, private）を必ず指定
        'visibility_required' => true,

        // PHPDocコメントの設定
        // 指定されたPHPDocタグ（param, return, throws, type, var）を整列
        'phpdoc_align' => [
            'tags' => ['param', 'return', 'throws', 'type', 'var'],
        ],

        // PHPDocブロック内の異なるセクション間に空白行を挿入
        'phpdoc_separation' => true,

        // PHPDocのサマリーの末尾にピリオドを追加
        'phpdoc_summary' => true,

        // PHPDocブロックの先頭と末尾の余分な空白を削除
        'phpdoc_trim' => true,
    ],
];
