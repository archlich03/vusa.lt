<template>
  <div>
    <QuickActionButton @click="showModal = true">{{ $t("Organizuoti focus grupę") }}
      <template #icon>
        <IFluentPeopleCommunity24Regular />
      </template>
    </QuickActionButton>
    <CardModal :title="$t('Organizuoti focus grupę')" :show="showModal" @close="showModal = false">
      <SpecialDoingForm :show-alert="showAlert" :form-template="doingTemplate" @alert-closed="showAlert = false"
        @submit:form="handleSubmitForm">
        <template #suggestion-content>
          <p class="mb-4">
            <strong> <i>Focus</i> grupės </strong> yra gyvi arba virtualūs
            susitikimai, kurie <i>fokusuoti</i> ties tam tikru klausimu.
          </p>
          <p class="mb-4">
            Paspausk <strong>„Pradėti!“</strong> ir sukurtame veiksmo šablone
            rasi visą informaciją apie tai, kaip organizuoti focus grupę.
          </p>
          <p>Važiuojam! ✊</p>
        </template>
      </SpecialDoingForm>
      <ModalHelperButton v-if="!showAlert" @click="showAlert = true" />
      <template #footer><span class="flex items-center gap-2 text-xs text-zinc-400">
          <IFluentInfo24Regular />
          <span>
            Sukurtą <i>focus</i> grupės šabloną galėsi bet kada ištrinti!
          </span>
        </span></template>
    </CardModal>
  </div>
</template>

<script setup lang="tsx">
import { trans as $t } from "laravel-vue-i18n";
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";

import { formatStaticTime } from "@/Utils/IntlTime";
import CardModal from "../Modals/CardModal.vue";
import ModalHelperButton from "./ModalHelperButton.vue";
import QuickActionButton from "./QuickActionButton.vue";
import SpecialDoingForm from "../AdminForms/Special/SpecialDoingForm.vue";

const timeIn7Days = new Date(
  new Date().getTime() + 7 * 24 * 60 * 60 * 1000
).getTime();

const institutionNameForTemplate = () => {
  const { auth } = usePage().props;

  let user = auth?.user;

  if (!user?.current_duties) {
    return null;
  }

  // check user.duties[].institution, and return only one if its not null
  let institution = user.current_duties
    .map((duty) => duty.institution)
    .filter((institution) => institution !== null)[0];

  return institution?.name;
};

const doingTemplate = {
  title: `${$t(
    "Studentų focus grupė"
  )} (${institutionNameForTemplate()}, ${formatStaticTime(timeIn7Days, {
    year: "numeric",
    month: "long",
  })})`,
  date: timeIn7Days,
  type: "focus-grupe",
};

const showModal = ref(false);
const showAlert = ref(true);

const handleSubmitForm = (model: Record<string, any>) => {
  router.post(route("doings.store"), model, {
    onSuccess: () => {
      showModal.value = false;
      showAlert.value = false;
    },
  });
};
</script>
