# Frontend Architecture

<cite>
**Referenced Files in This Document**   
- [app.ts](file://resources/js/app.ts)
- [ssr.ts](file://resources/js/ssr.ts)
- [AppLayout.vue](file://resources/js/layouts/AppLayout.vue)
- [AppSidebarLayout.vue](file://resources/js/layouts/app/AppSidebarLayout.vue)
- [AppShell.vue](file://resources/js/components/AppShell.vue)
- [AppSidebar.vue](file://resources/js/components/AppSidebar.vue)
- [AppSidebarHeader.vue](file://resources/js/components/AppSidebarHeader.vue)
- [Breadcrumbs.vue](file://resources/js/components/Breadcrumbs.vue)
- [Dashboard.vue](file://resources/js/pages/Dashboard.vue)
- [useAppearance.ts](file://resources/js/composables/useAppearance.ts)
- [useBreadcrumbs.ts](file://resources/js/composables/useBreadcrumbs.ts)
- [useNavigation.ts](file://resources/js/composables/useNavigation.ts)
- [useTwoFactorAuth.ts](file://resources/js/composables/useTwoFactorAuth.ts)
- [app.blade.php](file://resources/views/app.blade.php)
- [package.json](file://package.json)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [Composable Functions and State Management](#composable-functions-and-state-management)
7. [Inertia.js Integration and SSR](#inertiajs-integration-and-ssr)
8. [Component Hierarchy and UI Primitives](#component-hierarchy-and-ui-primitives)
9. [Responsive Design and Accessibility](#responsive-design-and-accessibility)
10. [Performance Optimization Techniques](#performance-optimization-techniques)

## Introduction
The CFCCashew frontend architecture is built on Vue 3 with TypeScript, leveraging the Composition API for modular and reusable code. The application uses Inertia.js to bridge Laravel's backend with a modern single-page application (SPA) experience, enabling server-side rendering (SSR), seamless page transitions, and shared state between server and client. This document details the architectural patterns, component structure, state management, and integration points that define the frontend implementation.

## Project Structure
The frontend codebase is organized under `resources/js/` with a clear separation of concerns:
- **components/**: Reusable UI components, including atomic primitives and composite layouts
- **composables/**: Custom Vue composables for shared logic (appearance, navigation, authentication)
- **layouts/**: Page layout wrappers (AppLayout, AuthLayout)
- **pages/**: Route-specific views (Dashboard, Bills, Containers, etc.)
- **types/**: TypeScript type definitions
- **app.ts**: Client-side application entry point
- **ssr.ts**: Server-side rendering entry point

```mermaid
graph TB
subgraph "Frontend Entry Points"
app_ts["app.ts"]
ssr_ts["ssr.ts"]
end
subgraph "Core Modules"
composables["composables/"]
components["components/"]
layouts["layouts/"]
pages["pages/"]
end
app_ts --> composables
app_ts --> components
app_ts --> layouts
app_ts --> pages
ssr_ts --> composables
ssr_ts --> components
ssr_ts --> layouts
ssr_ts --> pages
**Diagram sources**
- [app.ts](file://resources/js/app.ts)
- [ssr.ts](file://resources/js/ssr.ts)
- [package.json](file://package.json)
**Section sources**
- [app.ts](file://resources/js/app.ts)
- [ssr.ts](file://resources/js/ssr.ts)
- [package.json](file://package.json)
```

## Core Components
The core components form the foundation of the application's user interface and navigation system. These include layout wrappers, sidebar navigation, header components, and breadcrumb trails that maintain consistent UX across pages. The architecture emphasizes reusability and composability, with atomic UI elements built using the ShadCN design system principles.

**Section sources**
- [AppLayout.vue](file://resources/js/layouts/AppLayout.vue)
- [AppSidebarLayout.vue](file://resources/js/layouts/app/AppSidebarLayout.vue)
- [AppShell.vue](file://resources/js/components/AppShell.vue)
- [AppSidebar.vue](file://resources/js/components/AppSidebar.vue)

## Architecture Overview
The frontend architecture follows a layered approach with clear separation between presentation, logic, and integration layers. Vue 3's Composition API enables modular organization of reactive logic, while Inertia.js handles routing and state persistence between server and client.

```mermaid
graph TD
A["Laravel Backend"] --> |Inertia Response| B["Inertia.js"]
B --> C["Vue 3 Application"]
C --> D["Composition API"]
D --> E["Composables"]
E --> F["useAppearance"]
E --> G["useNavigation"]
E --> H["useBreadcrumbs"]
E --> I["useTwoFactorAuth"]
C --> J["UI Components"]
J --> K["Layouts"]
J --> L["Pages"]
J --> M["UI Primitives"]
B --> N["Server-Side Rendering"]
N --> O["ssr.ts"]
C --> P["TypeScript"]
P --> Q["Type Safety"]
P --> R["Enhanced DX"]
**Diagram sources**
- [app.ts](file://resources/js/app.ts)
- [ssr.ts](file://resources/js/ssr.ts)
- [package.json](file://package.json)
- [app.blade.php](file://resources/views/app.blade.php)
```

## Detailed Component Analysis

### Layout and Shell Components
The application uses a nested layout system with `AppLayout` serving as the primary wrapper that composes `AppSidebarLayout`. This layout provides consistent navigation, header, and content structure across authenticated routes.

#### Component Hierarchy
```mermaid
classDiagram
class AppLayout {
+breadcrumbs : BreadcrumbItemType[]
}
class AppSidebarLayout {
+breadcrumbs : BreadcrumbItemType[]
}
class AppShell {
+variant : 'header' | 'sidebar'
}
class AppSidebar {
+mainNavItems : NavItem[]
}
class AppSidebarHeader {
+breadcrumbs : BreadcrumbItemType[]
}
AppLayout --> AppSidebarLayout : "uses"
AppSidebarLayout --> AppShell : "wraps"
AppSidebarLayout --> AppSidebarHeader : "includes"
AppShell --> AppSidebar : "contains"
AppShell --> AppContent : "contains"
AppSidebarHeader --> Breadcrumbs : "uses"
**Diagram sources**
- [AppLayout.vue](file : //resources/js/layouts/AppLayout.vue)
- [AppSidebarLayout.vue](file : //resources/js/layouts/app/AppSidebarLayout.vue)
- [AppShell.vue](file : //resources/js/components/AppShell.vue)
- [AppSidebar.vue](file : //resources/js/components/AppSidebar.vue)
- [AppSidebarHeader.vue](file : //resources/js/components/AppSidebarHeader.vue)
```

**Section sources**
- [AppLayout.vue](file://resources/js/layouts/AppLayout.vue)
- [AppSidebarLayout.vue](file://resources/js/layouts/app/AppSidebarLayout.vue)
- [AppShell.vue](file://resources/js/components/AppShell.vue)
- [AppSidebar.vue](file://resources/js/components/AppSidebar.vue)
- [AppSidebarHeader.vue](file://resources/js/components/AppSidebarHeader.vue)

### Page Components
Page components like `Dashboard.vue` represent route-specific views that utilize the layout system and composables for dynamic functionality. These components are resolved by Inertia.js based on the current route.

```mermaid
sequenceDiagram
participant Browser
participant Laravel
participant Inertia
participant VueApp
participant Composables
Browser->>Laravel : Request /dashboard
Laravel->>Inertia : Return Inertia response
Inertia->>VueApp : Initialize Vue app
VueApp->>Composables : Call useBreadcrumbs()
Composables-->>VueApp : Return breadcrumb data
VueApp->>Browser : Render Dashboard with AppLayout
Browser-->>User : Display dashboard page
**Diagram sources**
- [Dashboard.vue](file : //resources/js/pages/Dashboard.vue)
- [app.ts](file : //resources/js/app.ts)
- [AppLayout.vue](file : //resources/js/layouts/AppLayout.vue)
- [useBreadcrumbs.ts](file : //resources/js/composables/useBreadcrumbs.ts)
```

**Section sources**
- [Dashboard.vue](file://resources/js/pages/Dashboard.vue)

## Composable Functions and State Management
The application leverages Vue 3's Composition API through custom composables that encapsulate reusable logic for appearance, navigation, breadcrumbs, pagination, and two-factor authentication.

### Key Composables
```mermaid
classDiagram
class useAppearance {
+initializeTheme()
+setAppearance(appearance)
+getAppearance()
}
class useBreadcrumbs {
+breadcrumbs : Ref<BreadcrumbItemType[]>
+generateBreadcrumbs()
}
class useNavigation {
+navigate(url)
+isActive(route)
}
class useTwoFactorAuth {
+setupTwoFactor()
+verifyCode(code)
+recoveryCodes : Ref<string[]>
}
**Diagram sources**
- [useAppearance.ts](file : //resources/js/composables/useAppearance.ts)
- [useBreadcrumbs.ts](file : //resources/js/composables/useBreadcrumbs.ts)
- [useNavigation.ts](file : //resources/js/composables/useNavigation.ts)
- [useTwoFactorAuth.ts](file : //resources/js/composables/useTwoFactorAuth.ts)
```

The state management pattern relies on Vue's reactivity system with `ref` and `reactive` for local component state, while Inertia.js manages the global page state that persists across requests. This hybrid approach provides both reactivity and server-side state consistency.

**Section sources**
- [useAppearance.ts](file://resources/js/composables/useAppearance.ts)
- [useBreadcrumbs.ts](file://resources/js/composables/useBreadcrumbs.ts)
- [useNavigation.ts](file://resources/js/composables/useNavigation.ts)
- [useTwoFactorAuth.ts](file://resources/js/composables/useTwoFactorAuth.ts)

## Inertia.js Integration and SSR
Inertia.js serves as the bridge between Laravel's backend and the Vue 3 frontend, enabling a seamless SPA-like experience without client-side routing. The integration supports server-side rendering for improved performance and SEO.

### SSR Flow
```mermaid
flowchart TD
A["HTTP Request"] --> B{SSR Enabled?}
B --> |Yes| C["Node Server"]
C --> D["ssr.ts"]
D --> E["createInertiaApp"]
E --> F["Resolve Page Component"]
F --> G["renderToString"]
G --> H["Return HTML"]
H --> I["Browser"]
B --> |No| J["app.ts"]
J --> K["Client-Side Mount"]
K --> I
I --> L["Hydration"]
**Diagram sources**
- [ssr.ts](file://resources/js/ssr.ts)
- [app.ts](file://resources/js/app.ts)
- [app.blade.php](file://resources/views/app.blade.php)
```

The `app.ts` file initializes the Inertia application, resolving page components dynamically using `resolvePageComponent` and Vite's import glob. The `ssr.ts` file provides server-side rendering capabilities using Vue's `renderToString` function.

**Section sources**
- [app.ts](file://resources/js/app.ts)
- [ssr.ts](file://resources/js/ssr.ts)
- [app.blade.php](file://resources/views/app.blade.php)

## Component Hierarchy and UI Primitives
The UI component system follows an atomic design pattern with primitives organized in the `components/ui/` directory. These components are built using the ShadCN approach with unstyled base components that receive styling via utility classes (Tailwind CSS).

### UI Component Structure
```mermaid
graph TD
A["UI Primitives"] --> B["Form Elements"]
A --> C["Navigation"]
A --> D["Layout"]
A --> E["Feedback"]
B --> Button
B --> Input
B --> Checkbox
B --> Textarea
B --> Select
C --> Sidebar
C --> NavigationMenu
C --> DropdownMenu
C --> Breadcrumb
D --> Card
D --> Sheet
D --> Dialog
D --> Separator
E --> Toast
E --> Skeleton
E --> Badge
E --> Tooltip
**Diagram sources**
- [components/ui/button/Button.vue](file://resources/js/components/ui/button/Button.vue)
- [components/ui/input/Input.vue](file://resources/js/components/ui/input/Input.vue)
- [components/ui/card/Card.vue](file://resources/js/components/ui/card/Card.vue)
- [components/Breadcrumbs.vue](file://resources/js/components/Breadcrumbs.vue)
```

Feature-specific components like `BillForm.vue` and `ContainerForm.vue` compose these primitives to create domain-specific interfaces, maintaining consistency while enabling specialized functionality.

**Section sources**
- [components/ui/](file://resources/js/components/ui)
- [components/AppContent.vue](file://resources/js/components/AppContent.vue)
- [components/NavMain.vue](file://resources/js/components/NavMain.vue)

## Responsive Design and Accessibility
The application implements responsive design principles using Tailwind CSS's mobile-first breakpoints and flexible layout utilities. The sidebar component adapts to different screen sizes with collapsible behavior, while content areas use responsive grid systems.

Accessibility is prioritized through semantic HTML, ARIA attributes in UI components, keyboard navigation support, and proper focus management. The `useNavigation` composable ensures keyboard accessibility for menu interactions, while form components include proper labeling and error messaging.

The application also supports dark mode through the `useAppearance` composable, which persists user preference in local storage and applies appropriate CSS classes to the document root.

**Section sources**
- [useAppearance.ts](file://resources/js/composables/useAppearance.ts)
- [AppShell.vue](file://resources/js/components/AppShell.vue)
- [components/ui/](file://resources/js/components/ui)

## Performance Optimization Techniques
The frontend architecture incorporates several performance optimizations:

1. **Code Splitting**: Inertia.js with Vite enables dynamic import of page components, reducing initial bundle size
2. **Server-Side Rendering**: SSR improves initial load performance and SEO
3. **Progressive Enhancement**: The Inertia progress indicator provides feedback during page transitions
4. **Efficient Re-renders**: Vue 3's reactivity system minimizes unnecessary component updates
5. **Tree-shakable Components**: UI components are exported individually, allowing unused components to be eliminated during bundling

The `initializeTheme()` function in `app.ts` runs synchronously during app initialization to prevent flash-of-wrong-theme issues, while the `useBreadcrumbs` composable computes breadcrumb trails reactively based on route parameters.

**Section sources**
- [app.ts](file://resources/js/app.ts)
- [useBreadcrumbs.ts](file://resources/js/composables/useBreadcrumbs.ts)
- [package.json](file://package.json)
- [vite.config.ts](file://vite.config.ts)