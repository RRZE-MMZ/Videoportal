{{--<x-list-clips :series="$series" :clips="$clips" :chapters="$chapters" dashboardAction="@can('menu-dashboard-admin')" />--}}
@livewire('list-series-clips',['series'=> $series])