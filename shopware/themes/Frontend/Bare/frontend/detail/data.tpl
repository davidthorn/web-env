{block name="frontend_detail_data"}
    {* Graduated prices *}
    {if $sArticle.sBlockPrices}
        {* Include block prices *}
        {block name="frontend_detail_data_block_price_include"}
            {include file="frontend/detail/block_price.tpl" sArticle=$sArticle}
        {/block}
    {else}
        <div class="product--price price--default{if $sArticle.has_pseudoprice} price--discount{/if}">

            {* Default price *}
            {block name='frontend_detail_data_price_configurator'}
                {if $sArticle.priceStartingFrom && !$sArticle.sConfigurator && $sView}
                    {* Price - Starting from *}
                    {block name='frontend_detail_data_price_configurator_starting_from_content'}
                        <span class="price--content content--starting-from">
                                {s name="DetailDataInfoFrom"}{/s} {$sArticle.priceStartingFrom|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                            </span>
                    {/block}
                {else}
                    {* Regular price *}
                    {block name='frontend_detail_data_price_default'}
                        <span class="price--content content--default">
                                <meta itemprop="price" content="{$sArticle.price|replace:',':'.'}">
                            {if $sArticle.priceStartingFrom}{s name='ListingBoxArticleStartsAt' namespace="frontend/listing/box_article"}{/s} {/if}{$sArticle.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                            </span>
                    {/block}
                {/if}
            {/block}

            {* Discount price *}
            {block name='frontend_detail_data_pseudo_price'}
                {if $sArticle.has_pseudoprice}

                    {block name='frontend_detail_data_pseudo_price_discount_icon'}
                        <span class="price--discount-icon">
                                <i class="icon--percent2"></i>
                            </span>
                    {/block}

                    {* Discount price content *}
                    {block name='frontend_detail_data_pseudo_price_discount_content'}
                        <span class="content--discount">
                            {block name='frontend_detail_data_pseudo_price_discount_before'}
                                {s name="priceDiscountLabel"}{/s}
                            {/block}
                            <span class="price--line-through">{$sArticle.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</span>

                            {block name='frontend_detail_data_pseudo_price_discount_after'}
                                {s name="priceDiscountInfo"}{/s}
                            {/block}

                            {* Percentage discount *}
                            {block name='frontend_detail_data_pseudo_price_discount_content_percentage'}
                                {if $sArticle.pseudopricePercent.float}
                                    <span class="price--discount-percentage">({$sArticle.pseudopricePercent.float|number}% {s name="DetailDataInfoSavePercent"}{/s})</span>
                                {/if}
                            {/block}
                        </span>
                    {/block}
                {/if}
            {/block}
        </div>
    {/if}

    {* Unit price *}
    {if $sArticle.purchaseunit}
        {block name='frontend_detail_data_price'}
            <div class='product--price price--unit'>

                {* Unit price label *}
                {block name='frontend_detail_data_price_unit_label'}
                    <span class="price--label label--purchase-unit">
                                {s name="DetailDataInfoContent"}{/s}
                            </span>
                {/block}

                {* Unit price content *}
                {block name='frontend_detail_data_price_unit_content'}
                    {$sArticle.purchaseunit} {$sArticle.sUnit.description}
                {/block}

                {* Unit price is based on a reference unit *}
                {if $sArticle.purchaseunit && $sArticle.referenceunit && $sArticle.purchaseunit != $sArticle.referenceunit && !$sArticle.sBlockPrices}
                    {* Reference unit price content *}
                    {block name='frontend_detail_data_price_unit_reference_content'}
                        ({$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                        / {$sArticle.referenceunit} {$sArticle.sUnit.description})
                    {/block}
                {/if}
            </div>
        {/block}
    {/if}

    {* Tax information *}
    {block name='frontend_detail_data_tax'}
        <p class="product--tax" data-content="" data-modalbox="true" data-targetSelector="a" data-mode="ajax">
            {s name="DetailDataPriceInfo"}{/s}
        </p>
    {/block}

    {if $sArticle.sBlockPrices && (!$sArticle.sConfigurator || $sArticle.pricegroupActive)}
        {foreach $sArticle.sBlockPrices as $blockPrice}
            {if $blockPrice.from == 1}
                <input id="price_{$sArticle.ordernumber}" type="hidden" value="{$blockPrice.price|replace:",":"."}">
            {/if}
        {/foreach}
    {/if}

    {block name="frontend_detail_data_delivery"}
        {* Delivery informations *}
        {if ($sArticle.sConfiguratorSettings.type != 1 && $sArticle.sConfiguratorSettings.type != 2) || $activeConfiguratorSelection == true}
            {include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sArticle}
        {elseif $sArticle.sReleaseDate && $sArticle.sReleaseDate|date_format:"%Y%m%d" > $smarty.now|date_format:"%Y%m%d"}
            <link itemprop="availability" href="https://schema.org/PreOrder" />
        {elseif $sArticle.esd}
            <link itemprop="availability" href="https://schema.org/InStock" />
        {elseif $sArticle.instock >= $sArticle.minpurchase}
            <link itemprop="availability" href="https://schema.org/InStock" />
        {else}
            <link itemprop="availability" href="https://schema.org/LimitedAvailability" />
        {/if}
    {/block}
{/block}
