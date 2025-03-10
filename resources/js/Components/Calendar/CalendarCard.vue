<template>
  <Card class="border shadow-xs transition-all duration-300  hover:shadow-md dark:border-zinc-200/20">
    <div class="h-32 w-full">
      <img v-if="calendarEvent.images && calendarEvent.images?.length > 0"
        class="size-full rounded-t-md object-cover object-center" :src="calendarEvent.images[0].original_url">
    </div>
    <CardHeader class="mt-2">
      <div class="align-center flex flex-row items-center p-2">
        <p class="line-clamp-2 w-full text-center text-xl font-bold leading-5">
          {{
            calendarEvent.title
          }}
        </p>
      </div>
    </CardHeader>
    <CardContent class="mb-2 flex flex-col gap-2 text-sm">
      <div class="inline-flex items-center gap-2">
        <IFluentCalendarLtr24Regular />
        <strong>
          {{
            formatStaticTime(
              new Date(calendarEvent.date),
              {
                year: "numeric",
                month: "numeric",
                day: "numeric",
                hour: "numeric",
                minute: "numeric",
              },
              $page.props.app.locale
            )
          }}
        </strong>
      </div>
      <div v-if="calendarEvent.end_date" class="inline-flex items-center gap-2">
        <IFluentTimer16Regular />
        <span>
          {{ formatStaticTime(
            new Date(calendarEvent.end_date),
            {
              year: "numeric",
              month: "numeric",
              day: "numeric",
              hour: "numeric",
              minute: "numeric",
            },
            $page.props.app.locale) }}
          <!-- {{ calculateEventDuration(calendarEvent) }} -->
        </span>
      </div>
      <div v-if="calendarEvent.location" class="inline-flex items-center gap-2">
        <IFluentLocation24Regular />
        <a class="underline" target="_blank"
          :href="`https://www.google.com/maps/search/?api=1&query=${calendarEvent.location}`">{{
            calendarEvent.location
          }}</a>
      </div>
      <div class="inline-flex items-center gap-2">
        <IFluentPeopleTeam24Regular />
        <span>
          {{ $t("Organizuoja") }}:
          <strong>{{ eventOrganizer }}</strong>
        </span>
      </div>
    </CardContent>

    <CardFooter v-if="!hideFooter">
      <div v-if="googleLink ||
        calendarEvent.cto_url ||
        calendarEvent.facebook_url
      " class="flex flex-col justify-center">
        <!-- <div class="flex flex-col justify-center text-xs leading-4"> -->
        <!--   {{ formatRelativeTime(new Date(calendarEvent.date)) }} -->
        <!-- </div> -->

        <NButton v-if="calendarEvent.tenant?.alias === 'mif' &&
          calendarEvent.category === 'freshmen-camps'
        " strong tag="a" round type="primary" @click="showModal = true">
          <template #icon>
            <IFluentHatGraduation20Filled />
          </template>
          {{ $t("Dalyvauk") }}!
        </NButton>
        <NButton v-if="calendarEvent.url" strong tag="a" round type="primary" target="_blank" :href="calendarEvent.url">
          <template #icon>
            <IFluentHatGraduation20Filled />
          </template>
          {{ $t("Dalyvauk") }}!
        </NButton>
        <!-- <NModal
          v-if="
            calendarEvent.tenant?.alias === 'mif' &&
            calendarEvent.category === 'freshmen-camps'
          "
          v-model:show="showModal"
          class="max-w-xl"
          display-directive="show"
          preset="card"
          title="VU MIF pirmakursių stovyklos registracija"
          :bordered="false"
        >
          <NScrollbar style="max-height: 600px"
            ><NMessageProvider><MIFCampRegistration /></NMessageProvider
          ></NScrollbar>
        </NModal> -->
        <div v-if="calendarEvent.facebook_url || googleLink" class="mt-2 flex justify-center gap-2">
          <NButton v-if="calendarEvent.facebook_url" title="Facebook" secondary tag="a" target="_blank"
            :href="calendarEvent.facebook_url" circle size="small">
            <IMdiFacebook />
          </NButton>
          <NPopover v-if="googleLink">
            {{ $t("Įsidėk į Google kalendorių") }}
            <template #trigger>
              <NButton secondary circle size="small" tag="a" target="_blank" :href="googleLink" @click.stop>
                <template #icon>
                  <IMdiGoogle />
                </template>
              </NButton>
            </template>
          </NPopover>
        </div>
      </div>
    </CardFooter>
  </Card>
</template>

<script setup lang="tsx">
import { trans as $t } from "laravel-vue-i18n";
import { computed, ref } from "vue";

import Card from "../ShadcnVue/ui/card/Card.vue";
import CardContent from '../ShadcnVue/ui/card/CardContent.vue';
import CardFooter from '../ShadcnVue/ui/card/CardFooter.vue';
import CardHeader from '../ShadcnVue/ui/card/CardHeader.vue';

import { formatStaticTime } from "@/Utils/IntlTime";

const props = defineProps<{
  calendarEvent: App.Entities.Calendar;
  googleLink?: string;
  hideFooter?: boolean;
}>();

const eventOrganizer = computed(() => {
  return (
    props.calendarEvent.organizer ??
    props.calendarEvent.tenant?.shortname
  );
});

const showModal = ref(false);

//const timeTillEvent = computed(() => {
//  const date = new Date(props.calendarEvent.date);
//  const now = new Date();
//  // get full days till event
//  const daysTillEvent = Math.floor(
//    (date.getTime() - now.getTime()) / (1000 * 60 * 60 * 24)
//  );
//  // get ms till event minus full days
//  const msTillEvent =
//    date.getTime() - now.getTime() - daysTillEvent * 1000 * 60 * 60 * 24;
//  return {
//    days: daysTillEvent,
//    ms: msTillEvent,
//  };
//});

//const calculateEventDuration = (event: App.Entities.Calendar) => {
//  if (!event.end_date) return undefined;
//
//  const startDate = new Date(event.date);
//  const endDate = new Date(event.end_date.replace(/-/g, "/"));
//  const duration = endDate.getTime() - startDate.getTime();
//
//  // if event is longer than 1 day, return days
//  if (duration > 1000 * 60 * 60 * 24) {
//    const days = Math.floor(duration / (1000 * 60 * 60 * 24));
//    return `${days} ${$t("dienos")}`;
//  }
//  // if event is longer than 1 hour, return hours
//  if (duration > 1000 * 60 * 60) {
//    const hours = Math.floor(duration / (1000 * 60 * 60));
//    return `${hours} ${$t("valandos")}`;
//  }
//  // if event is longer than 1 minute, return minutes
//  if (duration > 1000 * 60) {
//    const minutes = Math.floor(duration / (1000 * 60));
//    return `${minutes} ${$t("minutės")}`;
//  }
//};
</script>
