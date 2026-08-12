'use client';

import { useState } from 'react';

interface SearchHeaderProps {
  query: string;
  onSearch: (query: string) => void;
}

export default function SearchHeader({ query, onSearch }: SearchHeaderProps) {
  const [inputValue, setInputValue] = useState(query);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSearch(inputValue);
  };

  return (
    <div className="search-page-header">
      <h1 className="search-page-title">Search Results</h1>
      <form className="search-page-form" onSubmit={handleSubmit}>
        <div className="search-page-input-group">
          <i className="fa-sharp fa-solid fa-magnifying-glass input-icon"></i>
          <input 
            type="text" 
            name="q" 
            className="search-page-input" 
            placeholder="Search articles..." 
            autoComplete="off" 
            value={inputValue}
            onChange={(e) => setInputValue(e.target.value)}
          />
          <button type="submit" className="search-page-btn">
            <span>Search</span>
            <i className="fa-sharp fa-solid fa-arrow-right"></i>
          </button>
        </div>
      </form>
    </div>
  );
}