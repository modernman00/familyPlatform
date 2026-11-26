import { describe, expect, it } from 'vitest';
import { render, fireEvent, screen } from '@testing-library/preact';
import { FeedInteractiveShowcase, SHOWCASE_ITEMS } from './FeedInteractiveShowcase';

describe('FeedInteractiveShowcase (Live Playable Games & App Function Adverts)', () => {
    it('exports 12 experiences (6 playable games + 6 live app functions) and renders live Ayò gameplay', () => {
        expect(SHOWCASE_ITEMS.length).toBe(12);
        expect(SHOWCASE_ITEMS.filter((i) => i.category === 'game').length).toBe(6);
        expect(SHOWCASE_ITEMS.filter((i) => i.category === 'feature').length).toBe(6);

        render(<FeedInteractiveShowcase slotIndex={0} initialCategory="all" />);

        expect(screen.getByText(/Ayò Olópon — Yoruba Mancala Strategy/i)).toBeTruthy();
        expect(screen.getByText(/Treasury: 12 Seeds/i)).toBeTruthy();

        const pit1Btn = screen.getByRole('button', { name: /Sow Ayò Pit 1/i });
        fireEvent.click(pit1Btn);

        expect(screen.getByText(/Treasury: 16 Seeds/i)).toBeTruthy();
        expect(screen.getByText(/Jẹ mẹ́rin! You sowed Pit 1/i)).toBeTruthy();
    });

    it('switches between Playable Games and App Functions tabs and allows live sandbox interaction', () => {
        render(<FeedInteractiveShowcase slotIndex={0} initialCategory="all" />);

        const featuresTab = screen.getByRole('tab', { name: /App Functions \(6\)/i });
        fireEvent.click(featuresTab);

        expect(screen.getByText(/Respectful Kinship @Mentions & Elder Titles/i)).toBeTruthy();
        expect(screen.getByText(/@Grandpa’s house/i)).toBeTruthy();

        const childBtn = screen.getByRole('button', { name: /Son \/ Daughter Reader/i });
        fireEvent.click(childBtn);

        expect(screen.getByText(/@Dad’s house/i)).toBeTruthy();

        const nextBtn = screen.getByRole('button', { name: /Next interactive game or app feature/i });
        fireEvent.click(nextBtn);

        expect(screen.getByText(/7-Generation Dynasty Tree & Lineage Explorer/i)).toBeTruthy();
    });
});
