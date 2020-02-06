<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiLoginRequest;

class ApiLoginController extends Controller
{
    public function login(ApiLoginRequest $request)
    {
        $envr = App::environment('staging') ? 'testing.' : '';
        $username = strtolower($request->input('username'));
        $password = $request->input('password');
        $use_hmo_id = false;
        $user = null;
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = User::getUserByEmail($username);
        } elseif (ApiUtility::isHmoIdFormat($username)) {
            $enrolleeProfile = EnrolleeProfile::getEnrolleeProfileByHmoId($username);
            if (!$enrolleeProfile) {
                return response()->json(['status' => 'error', 'message' => 'HMO ID doesn\'t exist'], 401, $this->headers);
            }
            $user = $enrolleeProfile->user;
            $use_hmo_id = true;
        }
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => $use_hmo_id ? 'User Profile doesn\'t exist' : 'Email doesn\'t exist'], 401, $this->headers);
        }
        if (!User::validatePassword($user->email_address, $password)) {
            return response()->json(['status' => 'error', 'message' => 'Incorrect Credentials'], 401, $this->headers);
        }
        $userRoles = [];
        $userInfo['id'] = $user->id;
        $userInfo['first_name'] = $user->first_name;
        $userInfo['last_name'] = $user->last_name;
        $userInfo['email_address'] = $user->duplicate_email_address ? $user->duplicate_email_address : $user->email_address;
        $userInfo['phone_number'] = $user->phone_number ? ApiUtility::phoneNumberFromDBFormat($user->phone_number) : '';
        $userInfo['referral_code'] = $user->referral_code;
        $userInfo['access_token'] = $user->access_token;
        //ACCOUNT_OWNER - ACCOUNTS Access
        $accountManagerRoles = [Role::ACCOUNT_OWNER];
        foreach ($accountManagerRoles as $each) {
            if (UserToRole::userHasSpecificRole($user->id, $each)) {
                $has_role = true;
                break;
            }
        }
        $one = [];
        $one['name'] = 'user';
        $one['display_name'] = 'Manage Accounts';
        $one['can_access'] = $has_role ?? false;
        $one['url'] = 'https://accounts.'.ApiUtility::domainByEnvironment().'reliancehmo.com';
        array_push($userRoles, $one);
        //Enrollee Access
        $enrolleeRoles = [Role::ENROLLEE, Role::DEPENDANT];
        foreach ($enrolleeRoles as $each) {
            if (UserToRole::userHasSpecificRole($user->id, $each)) {
                $has_role = true;
                break;
            }
        }
        $one = [];
        $one['name'] = 'enrollee';
        $one['display_name'] = 'RelianceCare';
        $one['can_access'] = $has_role ?? false;
        $one['url'] = 'https://dashboard.'.ApiUtility::domainByEnvironment().'reliancehmo.com';
        array_push($userRoles, $one);
        if (!$use_hmo_id) {
            //Client access
            $clientAdminRoles = [Role::CLIENT_ADMINISTRATOR];
            foreach ($clientAdminRoles as $each) {
                if (UserToRole::userHasSpecificRole($user->id, $each)) {
                    $has_role = true;
                    break;
                }
            }
            $one = [];
            $one['name'] = 'client';
            $one['display_name'] = 'Company Dashboard';
            $one['can_access'] = $has_role ?? false;
            $one['url'] = 'https://client.'.ApiUtility::domainByEnvironment().'reliancehmo.com';
            array_push($userRoles, $one);
            //Provider Access
            $providerRoles = [Role::PROVIDER_MEDICAL_DIRECTOR, Role::HMO_MANAGER, Role::BILLING_OFFICER, Role::FRONTDESK_OFFICER];
            foreach ($providerRoles as $each) {
                if (UserToRole::userHasSpecificRole($user->id, $each)) {
                    $has_role = true;
                    break;
                }
            }
            $one = [];
            $one['name'] = 'provider';
            $one['display_name'] = 'Hospital Dashboard';
            $one['can_access'] = $has_role ?? false;
            $one['url'] = 'https://provider.'.ApiUtility::domainByEnvironment('old').'reliancehmo.com';
            array_push($userRoles, $one);
        }
        // affiliate role
        if (UserToRole::userHasSpecificRole($user->id, Role::AFFILIATE)) {
            $has_role = true;
        }
        $userRoles[] = [
            'name'         => 'affiliate',
            'display_name' => 'Affiliate Dashboard',
            'can_access'   => $has_role ?? false,
            'url'          => 'https://affiliates.'.($envr == 'testing.' ? 'staging.' : $envr).'reliancehmo.com',
        ];
        // doctor role
        if (UserToRole::userHasSpecificRole($user->id, Role::DOCTOR)) {
            $has_role = true;
        }
        // add doctor role if doctor profile exists and is active
        $doctorProfile = DoctorProfile::getDoctorProfileByUserId($user->id);
        if ($doctorProfile && $doctorProfile->active_status == ActiveStatus::ACTIVE) {
            $userRoles[] = [
                'name'         => 'doctor',
                'display_name' => 'Doctor Dashboard',
                'can_access'   => $has_role ?? false,
                'url'          => 'https://telemedicine.'.($envr == 'testing.' ? 'staging.' : $envr).'reliancehmo.com',
            ];
        }
        // partnership_agent role
        if (UserToRole::userHasSpecificRole($user->id, Role::PARTNERSHIP_AGENT)) {
            $has_role = true;
        }
        $userRoles[] = [
            'name'         => 'partnership_agent',
            'display_name' => 'Partnership Agent Dashboard',
            'can_access'   => $has_role ?? false,
            'url'          => 'https://partners.'.($envr == 'testing.' ? 'staging.' : $envr).'reliancehmo.com',
        ];
        //Logs in Login Table
        Login::create([
            'user_id'   => $user->id,
            'source_id' => Source::WEB_APP,
        ]);

        $data = [
            'basic_info' => $userInfo,
            'roles'      => $userRoles,
        ];

        return response()->json(['status' => 'success', 'data' =>  $data], 200, $this->headers);
    }
}
