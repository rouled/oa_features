{*
* 2017 Open agence
* @author Open agence <contact@open-agence.com>
* @copyright 2017 Open agence
*}

{if isset($oaFeatures) && $oaFeatures}
    <div class="product-oa-features oa-features clearfix">
        {foreach from=$oaFeatures item=features key=it}
            <ul class="af_item_group_{$it|escape:'htmlall':'UTF-8'}">
                {foreach from=$features.values key=index item=feature}
                    <li>
                        <img src="{$feature.image|escape:'htmlall':'UTF-8'}"
                             alt="{$feature.value|escape:'htmlall':'UTF-8'}"
                             title="{$feature.value|escape:'htmlall':'UTF-8'}" width="{$oaPictoWidth}"
                             height="{$oaPictoHeight}"/>
                    </li>
                {/foreach}
            </ul>
        {/foreach}
    </div>
{/if}