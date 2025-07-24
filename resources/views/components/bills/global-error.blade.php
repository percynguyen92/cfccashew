<template v-if="globalError">
    <div class="alert alert-error shadow-lg mb-6">
        <div>
            <i class="fas fa-exclamation-circle"></i>
            <span v-text="globalError"></span>
        </div>
        <button @click="globalError=''" class="btn btn-sm btn-circle btn-ghost">
            ✕
        </button>
    </div>
</template>
