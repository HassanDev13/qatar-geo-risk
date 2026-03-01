import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Progress } from "@/Components/ui/progress";
import { BookOpen, Eye, Star, Award } from "lucide-react";
import { router } from "@inertiajs/react";

export default function TermStatsModal({ isOpen, onClose, termGroup }) {
    if (!termGroup) return null;

    const maxCount = Math.max(...termGroup.resources.map((r) => r.count));

    const handleNavigateToTerm = (termId) => {
        // Check if user is authenticated
        const isAuthenticated =
            document.querySelector('meta[name="auth-user"]') !== null;

        if (isAuthenticated) {
            router.visit(route("terms.verify", termId));
        } else {
            router.visit(route("check.term", termId));
        }
    };

    const getConfidenceColor = (confidence) => {
        if (confidence >= 8) return "text-green-600 dark:text-green-400";
        if (confidence >= 6) return "text-yellow-600 dark:text-yellow-400";
        return "text-red-600 dark:text-red-400";
    };

    const getConsistencyColor = (consistency) => {
        if (consistency === "عالي") return "bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300";
        if (consistency === "متوسط") return "bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300";
        return "bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300";
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-5xl max-h-[90vh] overflow-y-auto bg-white dark:bg-gray-900">
                <DialogHeader>
                    <DialogTitle className="text-xl sm:text-2xl">
                        Term Statistics
                    </DialogTitle>
                    <DialogDescription>
                        Comprehensive analysis of this term across all resources
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-6">
                    {/* Term Display */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                English Term
                            </h3>
                            <p className="text-lg font-semibold text-gray-900 dark:text-white">
                                {termGroup.display_term_en || "N/A"}
                            </p>
                        </div>

                        <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Arabic Term
                            </h3>
                            <p
                                className="text-lg font-semibold text-gray-900 dark:text-white font-arabic text-right"
                                dir="rtl"
                            >
                                {termGroup.display_term_ar || "N/A"}
                            </p>
                        </div>
                    </div>

                    {/* Summary Statistics */}
                    <div className="grid grid-cols-3 gap-4">
                        <div className="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 text-center">
                            <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {termGroup.total_count}
                            </div>
                            <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Total Occurrences
                            </div>
                        </div>

                        <div className="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 text-center">
                            <div className="text-2xl font-bold text-green-600 dark:text-green-400">
                                {termGroup.resource_count}
                            </div>
                            <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Books/Resources
                            </div>
                        </div>

                        <div className="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800 text-center">
                            <div className="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {termGroup.variations.length}
                            </div>
                            <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Variations
                            </div>
                        </div>
                    </div>

                    {/* Variations */}
                    {termGroup.variations.length > 1 && (
                        <div>
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                Term Variations
                            </h3>
                            <div className="flex flex-wrap gap-2">
                                {termGroup.variations.map((variation, idx) => (
                                    <Badge
                                        key={idx}
                                        variant="secondary"
                                        className="text-sm"
                                    >
                                        {variation.term_en} / {variation.term_ar}{" "}
                                        ({variation.count})
                                    </Badge>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Resources List */}
                    <div>
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            Books & Resources
                        </h3>
                        <div className="space-y-4">
                            {termGroup.resources.map((resource) => {
                                const percentage =
                                    (resource.count / maxCount) * 100;

                                return (
                                    <div
                                        key={resource.resource_id}
                                        className="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                                    >
                                        <div className="flex items-start gap-2 mb-4">
                                            <BookOpen className="h-5 w-5 text-gray-500 mt-0.5 flex-shrink-0" />
                                            <div className="flex-1">
                                                <h4 className="font-medium text-gray-900 dark:text-white mb-1">
                                                    {resource.resource_name}
                                                </h4>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                                    {resource.count} total occurrences
                                                </p>
                                            </div>
                                        </div>

                                        {/* Arabic Term Details */}
                                        {resource.arabic_term_details && resource.arabic_term_details.length > 0 && (
                                            <div className="space-y-3 mt-4">
                                                {resource.arabic_term_details.map((arDetail, idx) => (
                                                    <div
                                                        key={idx}
                                                        className={`p-3 rounded-lg border-l-4 ${
                                                            arDetail.is_most_common
                                                                ? "border-amber-500 bg-amber-50 dark:bg-amber-900/20"
                                                                : "border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50"
                                                        }`}
                                                    >
                                                        {/* Arabic Term Header */}
                                                        <div className="flex items-start justify-between gap-2 mb-3">
                                                            <div className="flex-1">
                                                                <div className="flex items-center gap-2 mb-2">
                                                                    <span
                                                                        className="font-arabic text-base font-semibold text-gray-900 dark:text-white"
                                                                        dir="rtl"
                                                                    >
                                                                        {arDetail.arabic_term}
                                                                    </span>
                                                                    {arDetail.is_most_common && (
                                                                        <Badge className="bg-amber-500 text-white text-xs">
                                                                            <Award className="h-3 w-3 mr-1" />
                                                                            Most Common
                                                                        </Badge>
                                                                    )}
                                                                </div>
                                                                
                                                                {/* Statistics Row */}
                                                                <div className="flex flex-wrap gap-3 text-xs">
                                                                    <span className="text-gray-600 dark:text-gray-400">
                                                                        <strong>Count:</strong> {arDetail.count}
                                                                    </span>
                                                                    <span className="text-gray-600 dark:text-gray-400">
                                                                        <strong>Frequency:</strong> {arDetail.frequency}%
                                                                    </span>
                                                                    <span className={getConfidenceColor(arDetail.confidence)}>
                                                                        <strong>Confidence:</strong> {arDetail.confidence}/10
                                                                    </span>
                                                                </div>
                                                                
                                                                {/* Quality & Consistency Row */}
                                                                <div className="flex flex-wrap gap-3 mt-2 items-center">
                                                                    <div className="flex items-center gap-1">
                                                                        <span className="text-xs text-gray-600 dark:text-gray-400">
                                                                            <strong>Quality:</strong>
                                                                        </span>
                                                                        {[...Array(5)].map((_, i) => (
                                                                            <Star
                                                                                key={i}
                                                                                className={`h-3 w-3 ${
                                                                                    i < arDetail.stars
                                                                                        ? "text-amber-500 fill-amber-500"
                                                                                        : "text-gray-300 dark:text-gray-600"
                                                                                }`}
                                                                            />
                                                                        ))}
                                                                        <span className="text-xs text-gray-600 dark:text-gray-400 ml-1">
                                                                            ({arDetail.stars}/5)
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <Badge className={`text-xs ${getConsistencyColor(arDetail.consistency)}`}>
                                                                        {arDetail.consistency}
                                                                    </Badge>
                                                                </div>
                                                                
                                                                {/* Pages */}
                                                                {arDetail.pages && arDetail.pages.length > 0 && (
                                                                    <div className="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                                                        <strong>Pages:</strong> {arDetail.pages.join(", ")}
                                                                    </div>
                                                                )}
                                                            </div>
                                                            
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() =>
                                                                    handleNavigateToTerm(
                                                                        arDetail.term_ids[0],
                                                                    )
                                                                }
                                                                className="flex items-center gap-1 flex-shrink-0"
                                                            >
                                                                <Eye className="h-3 w-3" />
                                                                View
                                                            </Button>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        <Progress
                                            value={percentage}
                                            className="h-2 mt-4"
                                        />
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
