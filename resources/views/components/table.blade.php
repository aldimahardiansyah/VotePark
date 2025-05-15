<table class="table table-bordered">
    <thead>
        <tr>
            @foreach ($columns as $column)
                <th>{{ ucfirst($column) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        {{ $slot }}
    </tbody>
</table>
