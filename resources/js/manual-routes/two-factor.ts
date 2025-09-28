import {
    queryParams,
    type RouteDefinition,
    type RouteFormDefinition,
    type RouteQueryOptions,
} from '../wayfinder'
import { show as baseShow } from '../routes/two-factor'

type MethodOverride = 'DELETE' | 'PUT' | 'PATCH'

const withMethodOverride = (
    options: RouteQueryOptions | undefined,
    method: MethodOverride,
): RouteQueryOptions => {
    const key = options?.mergeQuery ? 'mergeQuery' : 'query'
    const existing = (
        (key === 'mergeQuery' ? options?.mergeQuery : options?.query) ?? {}
    ) as Record<string, unknown>

    return {
        ...(options ?? {}),
        [key]: {
            _method: method,
            ...existing,
        },
    } as RouteQueryOptions
}

export const show = baseShow

export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ['get', 'head'],
    url: '/two-factor-challenge',
} satisfies RouteDefinition<['get', 'head']>

login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
})

const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url(options),
    method: 'get',
})

loginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url(options),
    method: 'get',
})

loginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url({
        ...(options ?? {}),
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
})

login.form = loginForm

export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ['post'],
    url: '/two-factor-challenge',
} satisfies RouteDefinition<['post']>

store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

export const enable = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: enable.url(options),
    method: 'post',
})

enable.definition = {
    methods: ['post'],
    url: '/user/two-factor-authentication',
} satisfies RouteDefinition<['post']>

enable.url = (options?: RouteQueryOptions) => {
    return enable.definition.url + queryParams(options)
}

enable.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: enable.url(options),
    method: 'post',
})

const enableForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: enable.url(options),
    method: 'post',
})

enableForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: enable.url(options),
    method: 'post',
})

enable.form = enableForm

export const confirm = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(options),
    method: 'post',
})

confirm.definition = {
    methods: ['post'],
    url: '/user/confirmed-two-factor-authentication',
} satisfies RouteDefinition<['post']>

confirm.url = (options?: RouteQueryOptions) => {
    return confirm.definition.url + queryParams(options)
}

confirm.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(options),
    method: 'post',
})

const confirmForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: confirm.url(options),
    method: 'post',
})

confirmForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: confirm.url(options),
    method: 'post',
})

confirm.form = confirmForm

export const disable = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: disable.url(options),
    method: 'delete',
})

disable.definition = {
    methods: ['delete'],
    url: '/user/two-factor-authentication',
} satisfies RouteDefinition<['delete']>

disable.url = (options?: RouteQueryOptions) => {
    return disable.definition.url + queryParams(options)
}

disable.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: disable.url(options),
    method: 'delete',
})

const disableForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: disable.url(withMethodOverride(options, 'DELETE')),
    method: 'post',
})

disableForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: disable.url(withMethodOverride(options, 'DELETE')),
    method: 'post',
})

disable.form = disableForm

export const qrCode = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: qrCode.url(options),
    method: 'get',
})

qrCode.definition = {
    methods: ['get', 'head'],
    url: '/user/two-factor-qr-code',
} satisfies RouteDefinition<['get', 'head']>

qrCode.url = (options?: RouteQueryOptions) => {
    return qrCode.definition.url + queryParams(options)
}

qrCode.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: qrCode.url(options),
    method: 'get',
})

qrCode.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: qrCode.url(options),
    method: 'head',
})

export const secretKey = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: secretKey.url(options),
    method: 'get',
})

secretKey.definition = {
    methods: ['get', 'head'],
    url: '/user/two-factor-secret-key',
} satisfies RouteDefinition<['get', 'head']>

secretKey.url = (options?: RouteQueryOptions) => {
    return secretKey.definition.url + queryParams(options)
}

secretKey.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: secretKey.url(options),
    method: 'get',
})

secretKey.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: secretKey.url(options),
    method: 'head',
})

export const recoveryCodes = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: recoveryCodes.url(options),
    method: 'get',
})

recoveryCodes.definition = {
    methods: ['get', 'head'],
    url: '/user/two-factor-recovery-codes',
} satisfies RouteDefinition<['get', 'head']>

recoveryCodes.url = (options?: RouteQueryOptions) => {
    return recoveryCodes.definition.url + queryParams(options)
}

recoveryCodes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recoveryCodes.url(options),
    method: 'get',
})

recoveryCodes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: recoveryCodes.url(options),
    method: 'head',
})

export const regenerateRecoveryCodes = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: regenerateRecoveryCodes.url(options),
    method: 'post',
})

regenerateRecoveryCodes.definition = {
    methods: ['post'],
    url: '/user/two-factor-recovery-codes',
} satisfies RouteDefinition<['post']>

regenerateRecoveryCodes.url = (options?: RouteQueryOptions) => {
    return regenerateRecoveryCodes.definition.url + queryParams(options)
}

regenerateRecoveryCodes.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: regenerateRecoveryCodes.url(options),
    method: 'post',
})

const regenerateRecoveryCodesForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: regenerateRecoveryCodes.url(options),
    method: 'post',
})

regenerateRecoveryCodesForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: regenerateRecoveryCodes.url(options),
    method: 'post',
})

regenerateRecoveryCodes.form = regenerateRecoveryCodesForm

const twoFactor = {
    show: Object.assign(show, show),
    login: Object.assign(login, login),
    store: Object.assign(store, store),
    enable: Object.assign(enable, enable),
    confirm: Object.assign(confirm, confirm),
    disable: Object.assign(disable, disable),
    qrCode: Object.assign(qrCode, qrCode),
    secretKey: Object.assign(secretKey, secretKey),
    recoveryCodes: Object.assign(recoveryCodes, recoveryCodes),
    regenerateRecoveryCodes: Object.assign(
        regenerateRecoveryCodes,
        regenerateRecoveryCodes,
    ),
}

export default twoFactor
