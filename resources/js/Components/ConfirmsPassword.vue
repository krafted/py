<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>

        <app-modal
            :show="confirmingPassword"
            @close="confirmingPassword = false"
        >
            <template #icon>
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-gray-100 rounded-full dark:bg-black sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </template>

            <template #title>
                {{ title }}
            </template>

            <template #content>
                <div>
                    <div class="text-sm text-gray-500">
                        {{ content }}
                    </div>

                    <div class="mt-3 space-y-1">
                        <form-input
                            label="Password"
                            type="password"
                            ref="password"
                            v-model="form.password"
                            @keyup.enter.native="confirmPassword"
                        />

                        <form-input-error :message="form.error" />
                    </div>
                </div>
            </template>

            <template #actions>
                <app-button class="ml-3" @click.native="confirmPassword" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ button }}
                </app-button>

                <app-secondary-button @click.native="closeModal">
                    Nevermind
                </app-secondary-button>
            </template>
        </app-modal>
    </span>
</template>

<script>
    import { nextTick, provide, ref } from 'vue'
    import AppButton from '@/Components/Button'
    import AppModal from '@/Components/Modal'
    import AppSecondaryButton from '@/Components/SecondaryButton'
    import FormInput from '@/Components/Form/Input'
    import FormInputError from '@/Components/Form/InputError'

    export default {
        emits: ['confirmed'],
        props: {
            title: {
                default: 'Confirm Password',
            },
            content: {
                default: 'For your security, please confirm your password to continue.',
            },
            button: {
                default: 'Confirm',
            }
        },
        components: {
            AppButton,
            AppModal,
            AppSecondaryButton,
            FormInput,
            FormInputError,
        },
        setup(_, { emit }) {
            const password = ref(null)
            const confirmingPassword = ref(false)
            const form = ref({
                password: '',
                email: '',
            })
            const startConfirmingPassword = () => {
                axios.get(route('password.confirmation')).then(response => {
                    if (response.data.confirmed) emit('confirmed')
                    else {
                        confirmingPassword.value = true

                        setTimeout(() => password.value.focus(), 250)
                    }
                })
            }
            const confirmPassword = () => {
                form.value.processing = true

                axios.post(route('password.confirm'), {
                    password: form.value.password,
                }).then(async () => {
                    form.value.processing = false
                    closeModal()
                    await nextTick()
                    emit('confirmed')
                }).catch(error => {
                    form.value.processing = false
                    form.value.error = error.response.data.errors.password[0]
                    password.value.focus()
                })
            }
            const closeModal = () => {
                confirmingPassword.value = false
                form.value.password = ''
                form.value.error = ''
            }

            return {
                closeModal,
                confirmingPassword,
                confirmPassword,
                form,
                password,
                startConfirmingPassword,
            }
        },
    }
</script>