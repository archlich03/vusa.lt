<template>
  <div v-if="news.length > 0" class="my-4 rounded-lg py-4">
    <section class="grid gap-12 sm:grid-cols-1 md:grid-cols-[2fr_1fr]">
      <!-- <SmartLink v-for="item in news" :key="item.id" prefetch :href="route('news', { -->
      <!--   lang: item.lang, -->
      <!--   news: item.permalink ?? '', -->
      <!--   newsString: 'naujiena', -->
      <!--   subdomain: $page.props.tenant?.subdomain ?? 'www', -->
      <!-- })"> -->
      <!--   <NewsCard :news="item" /> -->
      <!-- </SmartLink> -->
      <div v-if="firstNews" class="max-h-120">
        <SmartLink :href="route('news', {
          lang: firstNews.lang,
          news: firstNews.permalink ?? '',
          newsString: 'naujiena',
          subdomain: $page.props.tenant?.subdomain ?? 'www',
        })">
          <img :src="firstNews.image" alt="News image"
            class="mb-1 max-h-80 xl:max-h-96 w-full rounded-xs object-cover shadow-md duration-200 hover:shadow-lg">
        </SmartLink>
        <p class="text-zinc-500 dark:text-zinc-400">
          {{ formatStaticTime(new Date(firstNews.publish_time), { year: "numeric", month: "long", day: "numeric" },
            $page.props.app.locale) }}
        </p>
        <SmartLink :href="route('news', {
          lang: firstNews.lang,
          news: firstNews.permalink ?? '',
          newsString: 'naujiena',
          subdomain: $page.props.tenant?.subdomain ?? 'www',
        })">
          <p
            class="mt-3 w-fit font-extrabold text-3xl leading-tighter text-zinc-800 line-clamp-2 dark:text-zinc-50 hover:text-vusa-red">
            {{ firstNews.title }}
          </p>
        </SmartLink>
        <div class="leading-tight mt-3 text-zinc-700 dark:text-zinc-300">
          <div v-html="firstNews.short" />
        </div>
      </div>
      <div class="flex flex-col gap-4">
        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
          {{ $t("Naujausios") }}
        </p>
        <div v-for="item in otherNews" :key="item.id" class="grid grid-cols-[1fr_2fr] gap-4 items-center">
          <SmartLink :href="route('news', {
            lang: item.lang,
            news: item.permalink ?? '',
            newsString: 'naujiena',
            subdomain: $page.props.tenant?.subdomain ?? 'www',
          })">
            <img :src="item.image" alt="News image"
              class="mb-1 max-h-32 md:max-h-40 w-full rounded-xs object-cover shadow-md duration-200 hover:shadow-lg">
          </SmartLink>
          <SmartLink :href="route('news', {
            lang: item.lang,
            news: item.permalink ?? '',
            newsString: 'naujiena',
            subdomain: $page.props.tenant?.subdomain ?? 'www',
          })">
            <span class="text-zinc-800 dark:text-zinc-200 text-lg md:text-sm hover:text-vusa-red font-semibold leading-tight lg:text-base line-clamp-3">
              {{ item.title }}
            </span>
          </SmartLink>
        </div>
        <SmartLink :href="route('newsArchive', {
          subdomain: $page.props.tenant?.subdomain ?? 'www',
          lang: $page.props.app.locale === 'lt' ? 'lt' : 'en',
          newsString: $page.props.app.locale === 'lt' ? 'naujienos' : 'news',
        })
          ">
          <div class="inline-flex items-center gap-1 font-bold">
            <span class="text-zinc-900 dark:text-zinc-100">{{ $t("Žiūrėti visas") }}</span>
            <IFluentArrowRight16Regular />
          </div>
        </SmartLink>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { trans as $t } from "laravel-vue-i18n";
import { usePage } from "@inertiajs/vue3";

import SmartLink from "./SmartLink.vue";
import type { News } from '@/Types/contentParts';
import { computed } from "vue";
import { formatStaticTime } from "@/Utils/IntlTime";

defineProps<{
  element: News;
}>();

const news = await fetch(
  route("api.news.tenant.index", {
    lang: usePage().props.app.locale,
    tenant: usePage().props.tenant?.alias,
  })
).then((response) => response.json()) as App.Entities.News[] | [];

const firstNews = computed(() => news[0]);

const otherNews = computed(() => news.slice(1));
</script>
