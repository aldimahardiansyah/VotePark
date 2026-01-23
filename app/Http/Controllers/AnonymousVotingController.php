<?php

namespace App\Http\Controllers;

use App\Models\VotingSession;
use App\Models\VotingCandidate;
use App\Models\VotingBallot;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnonymousVotingController extends Controller
{
    /**
     * Display a listing of voting sessions.
     */
    public function index()
    {
        $user = auth()->user();

        $query = VotingSession::with('candidates');
        if ($user->isSiteAdmin() && $user->site_id) {
            $query->where('site_id', $user->site_id);
        }

        $votingSessions = $query->orderBy('created_at', 'desc')->get();
        return view('contents.anonymous-voting.index', compact('votingSessions'));
    }

    /**
     * Show the form for creating a new voting session.
     */
    public function create()
    {
        $user = auth()->user();
        $sites = $user->isHoldingAdmin() ? Site::all() : collect();
        $userSiteId = $user->site_id;

        return view('contents.anonymous-voting.create', compact('sites', 'userSiteId'));
    }

    /**
     * Store a newly created voting session.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
        ];

        if ($user->isHoldingAdmin()) {
            $rules['site_id'] = 'nullable|exists:sites,id';
        }

        $request->validate($rules);

        $siteId = $user->isHoldingAdmin() ? $request->input('site_id') : $user->site_id;

        VotingSession::create([
            'title' => $request->title,
            'date' => $request->date,
            'site_id' => $siteId,
            'is_active' => true,
        ]);

        return redirect()->route('anonymous-voting.index')->with('success', 'Voting session created successfully.');
    }

    /**
     * Display the voting session with candidates and ballots.
     */
    public function show(VotingSession $anonymousVoting)
    {
        $anonymousVoting->load(['candidates', 'ballots']);
        return view('contents.anonymous-voting.show', compact('anonymousVoting'));
    }

    /**
     * Show the form for editing a voting session.
     */
    public function edit(VotingSession $anonymousVoting)
    {
        $user = auth()->user();
        $sites = $user->isHoldingAdmin() ? Site::all() : collect();
        $userSiteId = $user->site_id;

        return view('contents.anonymous-voting.edit', compact('anonymousVoting', 'sites', 'userSiteId'));
    }

    /**
     * Update the voting session.
     */
    public function update(Request $request, VotingSession $anonymousVoting)
    {
        $user = auth()->user();

        $rules = [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'is_active' => 'nullable|boolean',
        ];

        if ($user->isHoldingAdmin()) {
            $rules['site_id'] = 'nullable|exists:sites,id';
        }

        $request->validate($rules);

        $updateData = [
            'title' => $request->title,
            'date' => $request->date,
            'is_active' => $request->has('is_active'),
        ];

        if ($user->isHoldingAdmin()) {
            $updateData['site_id'] = $request->input('site_id');
        }

        $anonymousVoting->update($updateData);

        return redirect()->route('anonymous-voting.index')->with('success', 'Voting session updated successfully.');
    }

    /**
     * Remove the voting session.
     */
    public function destroy(VotingSession $anonymousVoting)
    {
        $anonymousVoting->delete();
        return redirect()->route('anonymous-voting.index')->with('success', 'Voting session deleted successfully.');
    }

    /**
     * Add a candidate to the voting session.
     */
    public function addCandidate(Request $request, VotingSession $votingSession)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sequence_number' => 'required|integer|min:1',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_candidate_' . $file->getClientOriginalName();
            $photoPath = $file->storeAs('voting_candidates/' . $votingSession->id, $filename, 'public');
        }

        VotingCandidate::create([
            'voting_session_id' => $votingSession->id,
            'name' => $request->name,
            'sequence_number' => $request->sequence_number,
            'photo' => $photoPath,
        ]);

        return redirect()->route('anonymous-voting.show', $votingSession->id)->with('success', 'Candidate added successfully.');
    }

    /**
     * Update a candidate.
     */
    public function updateCandidate(Request $request, VotingCandidate $candidate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sequence_number' => 'required|integer|min:1',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $updateData = [
            'name' => $request->name,
            'sequence_number' => $request->sequence_number,
        ];

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($candidate->photo) {
                Storage::disk('public')->delete($candidate->photo);
            }
            $file = $request->file('photo');
            $filename = time() . '_candidate_' . $file->getClientOriginalName();
            $updateData['photo'] = $file->storeAs('voting_candidates/' . $candidate->voting_session_id, $filename, 'public');
        }

        $candidate->update($updateData);

        return redirect()->route('anonymous-voting.show', $candidate->voting_session_id)->with('success', 'Candidate updated successfully.');
    }

    /**
     * Delete a candidate.
     */
    public function deleteCandidate(VotingCandidate $candidate)
    {
        $votingSessionId = $candidate->voting_session_id;

        // Delete photo if exists
        if ($candidate->photo) {
            Storage::disk('public')->delete($candidate->photo);
        }

        $candidate->delete();

        return redirect()->route('anonymous-voting.show', $votingSessionId)->with('success', 'Candidate deleted successfully.');
    }

    /**
     * Record a ballot (vote).
     */
    public function recordBallot(Request $request, VotingSession $votingSession)
    {
        $request->validate([
            'voting_candidate_id' => 'required|exists:voting_candidates,id',
            'npp' => 'required|numeric|min:0',
            'npp_code' => 'nullable|string|max:255',
        ]);

        // Verify candidate belongs to this session
        $candidate = VotingCandidate::where('id', $request->voting_candidate_id)
            ->where('voting_session_id', $votingSession->id)
            ->firstOrFail();

        VotingBallot::create([
            'voting_session_id' => $votingSession->id,
            'voting_candidate_id' => $request->voting_candidate_id,
            'npp' => $request->npp,
            'npp_code' => $request->npp_code,
        ]);

        return redirect()->route('anonymous-voting.show', $votingSession->id)->with('success', 'Ballot recorded successfully.');
    }

    /**
     * Delete a ballot.
     */
    public function deleteBallot(VotingBallot $ballot)
    {
        $votingSessionId = $ballot->voting_session_id;
        $ballot->delete();

        return redirect()->route('anonymous-voting.show', $votingSessionId)->with('success', 'Ballot deleted successfully.');
    }

    /**
     * Display the projector presentation view.
     */
    public function presentation(VotingSession $votingSession)
    {
        return view('contents.anonymous-voting.presentation', compact('votingSession'));
    }
}
