{extends file='frontend/index/header.tpl'}

{* Title *}
{block name='frontend_index_header_title'}{strip}
    {if $sArticle.metaTitle}
        {$sArticle.metaTitle|escapeHtml} | {{config name="sShopname"}|escapeHtml}
    {elseif $sCategoryContent.metaTitle}
        {$sCategoryContent.metaTitle|escapeHtml} | {{config name="sShopname"}|escapeHtml}
    {else}
        {$smarty.block.parent}
    {/if}
{/strip}{/block}

{block name='frontend_index_header_meta_tags_opengraph'}
    {if $sArticle}
        <meta property="og:type" content="article" />
        <meta property="og:site_name" content="{{config name="sShopname"}|escapeHtml}" />
        <meta property="og:title" content="{$sArticle.title|escapeHtml}" />
        <meta property="og:description" content="{$sArticle.description|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}" />

        {if $sArticle.author}
        <meta property="article:author" content="{$sArticle.author.name|escapeHtml}" />
        {/if}

        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="{$sArticle.title|escapeHtml}" />
        <meta name="twitter:description" content="{$sArticle.description|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}" />

        {if $sArticle.preview.source}
            <meta property="og:image" content="{$sArticle.preview.source}" />
            <meta name="twitter:image" content="{$sArticle.preview.source}" />
        {/if}
    {else}
        {s name="IndexMetaDescriptionStandard" assign="description"}{/s}
        {if $sCategoryContent.cmstext}
            {$description = "{$sCategoryContent.cmstext|trim|strip_tags|escapeHtml}"}
        {elseif $sCategoryContent.metaDescription}
            {$description = "{$sCategoryContent.metaDescription|trim|strip_tags|escapeHtml}"}
        {/if}

        {$description = $description|trim|truncate:$SeoDescriptionMaxLength:'…'}

        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{{config name="sShopname"}|escapeHtml}" />
        <meta property="og:title" content="{$sCategoryContent.name|escapeHtml}" />
        <meta property="og:description" content="{$description|escapeHtml}" />

        <meta name="twitter:card" content="summary" />
        <meta name="twitter:site" content="{{config name="sShopname"}|escapeHtml}" />
        <meta name="twitter:title" content="{$sCategoryContent.name|escapeHtml}" />
        <meta name="twitter:description" content="{$description|escapeHtml}" />
    {/if}
{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sArticle.metaKeyWords}{$sArticle.metaKeyWords|escapeHtml}{else}{if $sCategoryContent.metaKeywords}{$sCategoryContent.metaKeywords|escapeHtml}{/if}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sArticle.metaDescription}{$sArticle.metaDescription|strip_tags|escape}{else}{if $sCategoryContent.metaDescription}{$sCategoryContent.metaDescription|strip_tags|escape}{/if}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    {* Count of available product pages *}
    {$pages = ceil($sNumberArticles / $sPerPage)}

    {if $sArticle}
        <link rel="canonical" href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}" />
    {elseif {config name="seoIndexPaginationLinks"} && $pages > 1}

        {* Previous rel tag *}
        {if $sPage > 1}
            {$sCategoryContent.canonicalParams.sPage = $sPage - 1}
            <link rel="prev" href="{url params = $sCategoryContent.canonicalParams}">
        {/if}

        {* Next rel tag *}
        {if $pages >= $sPage + 1}
            {$sCategoryContent.canonicalParams.sPage = $sPage + 1}
            <link rel="next" href="{url params = $sCategoryContent.canonicalParams}">
        {/if}
    {elseif !{config name="seoIndexPaginationLinks"}}
        <link rel="canonical" href="{if $sCategoryContent.canonicalParams}{url params = $sCategoryContent.canonicalParams}{/if}" />
    {/if}
{/block}


{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}
<link rel="alternate" type="application/rss+xml" title="{$sCategoryContent.description|escape} RSS"
      href="{$sCategoryContent.rssFeed}"/>
<link rel="alternate" type="application/atom+xml" title="{$sCategoryContent.description|escape} ATOM"
      href="{$sCategoryContent.atomFeed}"/>
{/block}
