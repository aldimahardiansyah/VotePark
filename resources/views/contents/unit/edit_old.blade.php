<x-layout.main>
    <x-slot name="title">Edit Unit</x-slot>

    <div class="container">
        <h1>Edit Unit</h1>
        <hr>

        <x-alert.success-and-error />

        <form action="{{ route('unit.update', [$unit->id, 'password=' . request('password')]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $unit->code }}" required readonly>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="npp" class="form-label">NPP</label>
                        <input type="text" class="form-control" id="npp" name="npp" value="{{ $unit->npp }}">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="wide" class="form-label">Luas</label>
                        <input type="text" class="form-control" id="wide" name="wide" value="{{ $unit->wide }}">
                    </div>
                </div>
            </div>
            <hr>

            <div class="mb-3">
                <label for="user_name" class="form-label">Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" value="{{ $unit->user->name }}">
            </div>
            <div class="mb-3">
                <label for="user_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="user_email" name="user_email" value="{{ $unit->user->email }}">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>

        <a href="{{ route('unit.index') }}" class="btn btn-secondary mt-3">Back to Units</a>
    </div>
</x-layout.main>
