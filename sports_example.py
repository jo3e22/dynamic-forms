#!/usr/bin/env python3
"""
Sports Organization Form Integration Example

This script demonstrates how to use the Dynamic Forms API
for managing event registrations.

Usage:
  python sports_example.py --create      # Create a new registration form
  python sports_example.py --submit      # Submit some test registrations
  python sports_example.py --retrieve    # Get all registrations
  python sports_example.py --export      # Export to CSV
"""

import requests
import json
import csv
import argparse
from datetime import datetime
from typing import Dict, List, Any

# Configuration
API_BASE = "http://localhost:8001/api"
SANCTUM_TOKEN = "1|3K23HK5qvDUgKJxNpzL9f0aJxv7NTChSfuY6xIzPd9050e9e"  # Test token

class SportsFormAPI:
    def __init__(self, base_url: str, token: str):
        self.base_url = base_url
        self.token = token
        self.form_id = None
        self.api_key = None
        
    def create_competition_form(self) -> Dict[str, Any]:
        """Create a U14 competition registration form"""
        
        payload = {
            "name": "U14 Championship Registration 2026",
            "description": "Register your team for the U14 Championship. Deadline: March 31, 2026",
            "schema": [
                {
                    "name": "club_name",
                    "type": "text",
                    "label": "Club Name",
                    "required": True
                },
                {
                    "name": "team_name",
                    "type": "text",
                    "label": "Team Name",
                    "required": True
                },
                {
                    "name": "coach_email",
                    "type": "email",
                    "label": "Coach Email Address",
                    "required": True
                },
                {
                    "name": "num_players",
                    "type": "number",
                    "label": "Number of Players (max 14)",
                    "required": True
                },
                {
                    "name": "division",
                    "type": "select",
                    "label": "Division",
                    "required": True,
                    "options": ["Div A", "Div B", "Div C"]
                },
                {
                    "name": "notes",
                    "type": "textarea",
                    "label": "Any special notes",
                    "required": False
                }
            ]
        }
        
        response = requests.post(
            f"{self.base_url}/forms",
            json=payload,
            headers={"Authorization": f"Bearer {self.token}"}
        )
        
        if response.status_code in [200, 201]:
            try:
                data = response.json()
                if 'form' in data:
                    data = data['form']
                self.form_id = data.get('id')
                self.api_key = data.get('api_key')
                
                print("✓ Form created successfully!")
                print(f"  Form ID: {self.form_id}")
                print(f"  API Key: {self.api_key}")
                if 'code' in data:
                    print(f"  Form Code: {data['code']}")
                
                return data
            except json.JSONDecodeError:
                print(f"✗ Server returned invalid JSON")
                print(f"  Status: {response.status_code}")
                print(f"  Response: {response.text[:200]}")
                return None
        else:
            print(f"✗ Error creating form: {response.status_code}")
            try:
                print(response.json())
            except:
                print(response.text[:200])
            return None
    
    def submit_registrations(self, registrations: List[Dict]) -> None:
        """Submit multiple team registrations"""
        
        if not self.form_id:
            print("✗ No form ID set. Create a form first.")
            return
        
        for reg in registrations:
            response = requests.post(
                f"{self.base_url}/forms/{self.form_id}/submissions",
                json=reg,
                headers={"X-API-Client": "sports-org-admin"}
            )
            
            if response.status_code == 201:
                try:
                    data = response.json()
                    code = data.get('submission', {}).get('code', '?')
                    print(f"✓ {reg['club_name']} - {reg['team_name']} registered (code: {code})")
                except:
                    print(f"✓ {reg['club_name']} - {reg['team_name']} registered")
            else:
                print(f"✗ Failed to register {reg['club_name']}: {response.status_code}")
                try:
                    print(f"  {response.json()}")
                except:
                    print(f"  {response.text[:100]}")
    
    def get_registrations(self) -> List[Dict]:
        """Retrieve all registrations for the form"""
        
        if not self.form_id or not self.api_key:
            print("✗ No form ID or API key set.")
            return []
        
        all_registrations = []
        page = 1
        
        while True:
            response = requests.get(
                f"{self.base_url}/forms/{self.form_id}/submissions?page={page}&limit=100",
                headers={"Authorization": f"Bearer {self.api_key}"}
            )
            
            if response.status_code != 200:
                print(f"✗ Error retrieving registrations: {response.status_code}")
                break
            
            data = response.json()
            all_registrations.extend(data['data'])
            
            pagination = data['pagination']
            print(f"  Retrieved page {pagination['current_page']}/{pagination['last_page']}")
            
            if page >= pagination['last_page']:
                break
            
            page += 1
        
        print(f"✓ Retrieved {len(all_registrations)} registrations total")
        return all_registrations
    
    def export_to_csv(self, registrations: List[Dict], filename: str = None) -> None:
        """Export registrations to CSV"""
        
        if not registrations:
            print("✗ No registrations to export")
            return
        
        if filename is None:
            filename = f"registrations_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
        
        # Extract field names from first registration - fields might be a dict or list
        first_reg = registrations[0]
        fields = first_reg.get('fields', {})
        if isinstance(fields, list):
            # If fields is a list, we can't export it as CSV easily
            print(f"⚠ Warning: Fields data is in an unexpected format (list). Exporting basic info only.")
            fieldnames = ['code', 'email', 'status', 'submitted_at']
        else:
            fieldnames = list(fields.keys())
            fieldnames.insert(0, 'submitted_at')
            fieldnames.insert(0, 'email')
            fieldnames.insert(0, 'code')
        
        with open(filename, 'w', newline='') as csvfile:
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            writer.writeheader()
            
            for reg in registrations:
                row = {
                    'code': reg.get('code'),
                    'email': reg.get('email'),
                    'submitted_at': reg.get('submitted_at'),
                }
                
                fields_data = reg.get('fields', {})
                if isinstance(fields_data, dict):
                    row.update(fields_data)
                
                writer.writerow(row)
        
        print(f"✓ Exported {len(registrations)} registrations to {filename}")


def main():
    parser = argparse.ArgumentParser(
        description='Sports Organization Form API Example'
    )
    parser.add_argument(
        '--create',
        action='store_true',
        help='Create a new registration form'
    )
    parser.add_argument(
        '--submit',
        action='store_true',
        help='Submit test registrations'
    )
    parser.add_argument(
        '--retrieve',
        action='store_true',
        help='Retrieve all registrations'
    )
    parser.add_argument(
        '--export',
        action='store_true',
        help='Export registrations to CSV'
    )
    parser.add_argument(
        '--form-id',
        type=int,
        help='Form ID to use'
    )
    parser.add_argument(
        '--api-key',
        type=str,
        help='API key to use'
    )
    
    args = parser.parse_args()
    
    # Initialize API client
    api = SportsFormAPI(API_BASE, SANCTUM_TOKEN)
    
    if args.form_id:
        api.form_id = args.form_id
    if args.api_key:
        api.api_key = args.api_key
    
    # Create form
    if args.create:
        print("Creating competition registration form...")
        api.create_competition_form()
        print()
    
    # Submit test registrations
    if args.submit:
        print("Submitting test registrations...")
        test_registrations = [
            {
                "club_name": "City United",
                "team_name": "Youth A",
                "coach_email": "coach@cityunited.com",
                "num_players": 14,
                "division": "Div A",
                "notes": "Looking forward to the tournament!"
            },
            {
                "club_name": "Metro Stars",
                "team_name": "Development",
                "coach_email": "dev@metrostars.com",
                "num_players": 12,
                "division": "Div B",
                "notes": "First time competing"
            },
            {
                "club_name": "Riverside FC",
                "team_name": "Premiers",
                "coach_email": "premier@riverside.com",
                "num_players": 14,
                "division": "Div A",
                "notes": None
            }
        ]
        
        api.submit_registrations(test_registrations)
        print()
    
    # Retrieve registrations
    if args.retrieve:
        print("Retrieving registrations...")
        registrations = api.get_registrations()
        
        if registrations:
            print("\nRegistrations:")
            for reg in registrations:
                print(f"  - {reg['fields'].get('club_name')} ({reg['code']})")
        print()
    
    # Export to CSV
    if args.export:
        print("Exporting registrations to CSV...")
        registrations = api.get_registrations()
        api.export_to_csv(registrations)


if __name__ == '__main__':
    # If no args provided, run a demo
    import sys
    if len(sys.argv) == 1:
        print("Sports Organization Form Integration Example")
        print("=" * 50)
        print()
        
        # Demo flow
        api = SportsFormAPI(API_BASE, SANCTUM_TOKEN)
        
        # 1. Create form
        print("Step 1: Creating competition registration form...")
        form = api.create_competition_form()
        print()
        
        # 2. Submit registrations
        print("Step 2: Submitting sample registrations...")
        test_registrations = [
            {
                "club_name": "City United",
                "team_name": "Youth A",
                "coach_email": "coach@cityunited.com",
                "num_players": 14,
                "division": "Div A",
                "notes": "Ready to compete!"
            }
        ]
        api.submit_registrations(test_registrations)
        print()
        
        # 3. Retrieve registrations
        print("Step 3: Retrieving all registrations...")
        registrations = api.get_registrations()
        print()
        
        # 4. Export to CSV
        if registrations:
            print("Step 4: Exporting to CSV...")
            api.export_to_csv(registrations)
    else:
        main()
